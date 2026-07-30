<?php

namespace App\Modules\Chatbot\Http\Controllers\Client;

use App\Models\ChatbotFaq;
use App\Models\ChatbotMessage;
use App\Models\Vehicle;
use App\Services\ChatbotAppointmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Jobs\NotifyAdvisorsOfChatbotQuery;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotAppointmentService $appointments,
    ) {}

    public function index(Request $request)
    {
        $messages = ChatbotMessage::where('user_id', $request->user()->id)
            ->orderBy('created_at')
            ->take(50)
            ->get();

        $faqs = ChatbotFaq::where('is_active', true)->orderBy('sort_order')->get();

        return view('chatbot::client.chatbot.index', compact('messages', 'faqs'));
    }

    public function message(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $text = trim($validated['message']);

        ChatbotMessage::create([
            'user_id' => $user->id,
            'session_id' => $request->session()->getId(),
            'sender' => 'user',
            'message' => $text,
        ]);

        $reply = $this->generateReply($user, $text);

        ChatbotMessage::create([
            'user_id' => $user->id,
            'session_id' => $request->session()->getId(),
            'sender' => 'bot',
            'message' => $reply,
        ]);

        return response()->json(['reply' => $reply]);
    }

    private function generateReply($user, string $text): string
    {
        $lower = Str::lower($text);

        if (Str::contains($lower, ['cancelar cita', 'cancelar solicitud'])) {
            $this->appointments->cancelDraft();

            return 'Solicitud de cita cancelada. Si necesitas otra cosa, escríbeme.';
        }

        if ($this->appointments->shouldHandle($text)) {
            return $this->appointments->handle($user, $text);
        }

        if ($this->isGreeting($lower)) {
            return $this->greetingReply($user);
        }

        if ($plate = $this->extractPlate($text)) {
            $vehicle = $this->resolveUserVehicle($user, $plate);
            if ($vehicle) {
                return $this->appendNextAction($this->buildVehicleDetails($vehicle));
            }

            return 'No encontré un vehículo con esa placa en tu cuenta.';
        }

        if ($this->isVehicleStatusRequest($lower) || ($this->isGeneralVehicleInquiry($lower) && $this->extractPlate($text) === null)) {
            $vehicles = $user->vehicles()->orderBy('id')->get();
            $count = $vehicles->count();

            if ($count === 0) {
                return 'No tienes vehículos registrados. Contacta al taller para registrarlos.';
            }

            if ($count === 1) {
                $vehicle = $vehicles->first();

                return $this->appendNextAction($this->buildVehicleDetails($vehicle));
            }

            return $this->appendNextAction($this->buildVehicleSummary($vehicles));
        }

        if ($this->looksLikeVehicleQuery($lower, $text)) {
            $plate = $this->extractPlate($text);
            if ($plate) {
                $vehicle = $this->resolveUserVehicle($user, $plate);
                if ($vehicle) {
                    return $this->appendNextAction($this->buildVehicleDetails($vehicle));
                }

                return 'No encontré un vehículo con esa placa en tu cuenta.';
            }

            $count = $user->vehicles()->count();
            if ($count === 0) {
                return 'No tienes vehículos registrados. Contacta al taller para registrarlos.';
            }

            return "Tienes {$count} vehículo(s) registrado(s). Indícame la placa para consultar el estado detallado.";
        }

        if (Str::contains($lower, ['gasto', 'gastos', 'costo', 'precio', 'cuanto'])) {
            $total = $user->vehicles()
                ->join('maintenances', 'vehicles.id', '=', 'maintenances.vehicle_id')
                ->where('maintenances.status', 'completado')
                ->where('maintenances.performed_at', '>=', now()->subMonths(12))
                ->sum('maintenances.cost');

            return 'En los últimos 12 meses has invertido $'.number_format($total, 2).' en mantenimientos. Consulta la sección Gastos para el detalle.';
        }

        if (Str::contains($lower, ['proximo', 'próximo', 'programad', 'proxima cita', 'próxima cita', 'siguiente cita'])) {
            $next = $user->vehicles()
                ->join('maintenance_schedules', 'vehicles.id', '=', 'maintenance_schedules.vehicle_id')
                ->where('maintenance_schedules.status', 'programado')
                ->where('maintenance_schedules.scheduled_date', '>=', now()->toDateString())
                ->orderBy('maintenance_schedules.scheduled_date')
                ->select('maintenance_schedules.*', 'vehicles.plate')
                ->first();

            if ($next) {
                return "Próximo servicio: {$next->title} para {$next->plate} el ".date('d/m/Y', strtotime($next->scheduled_date)).'.';
            }

            return 'No tienes mantenimientos programados próximamente.';
        }

        $faq = ChatbotFaq::where('is_active', true)
            ->get()
            ->first(function ($item) use ($lower) {
                foreach (explode(',', $item->keywords ?? '') as $keyword) {
                    $keyword = trim(Str::lower($keyword));
                    if ($keyword !== '' && Str::contains($lower, $keyword)) {
                        return true;
                    }
                }

                return Str::contains($lower, Str::lower($item->question));
            });

        if ($faq) {
            return $faq->answer;
        }

        NotifyAdvisorsOfChatbotQuery::dispatch($user, $text);

        return 'No pude entender la información proporcionada.\n\nSi deseas agendar una cita, indícame una fecha válida, por ejemplo:\n\n• mañana\n• viernes\n• 15/08/2026';
    }

    private function extractPlate(string $text): ?string
    {
        if (preg_match('/\b([A-Z]{2,3}[-\s]?\d{2,4})\b/i', $text, $matches)) {
            return $this->normalizePlate($matches[1]);
        }

        return null;
    }

    private function buildVehicleDetails(Vehicle $vehicle): string
    {
        $order = $vehicle->serviceOrders()->latest()->first();
        $details = "Estado del vehículo:\n\n🚙 {$vehicle->brand} {$vehicle->model}\nPlaca: {$vehicle->plate}\nEstado: {$vehicle->statusLabel()}";

        if ($order) {
            $details .= "\nÚltima orden: {$order->description}\nEstado de la orden: {$order->statusLabel()}";
            $details .= "\nFecha de ingreso: {$order->created_at?->format('d/m/Y') ?? '—'}";
        }

        return $details;
    }

    private function buildVehicleSummary($vehicles): string
    {
        $summaries = $vehicles
            ->map(fn (Vehicle $vehicle) => "🚗 {$vehicle->brand} {$vehicle->model} ({$vehicle->plate})\nEstado: {$vehicle->statusLabel()}")
            ->join("\n\n");

        return "Tienes {$vehicles->count()} vehículos registrados:\n\n{$summaries}\n\nEscribe la placa del vehículo sobre el que deseas obtener más información.";
    }

    private function appendNextAction(string $message): string
    {
        return trim($message)."\n\n¿Puedo ayudarte con algo más?\n\n1️⃣ Consultar estado del vehículo\n2️⃣ Agendar una cita\n3️⃣ Consultar historial\n4️⃣ Finalizar conversación";
    }

    private function greetingReply($user): string
    {
        $name = trim($user->name ?? '');
        $namePart = $name !== '' ? ", {$name}" : '';

        return "¡Hola{$namePart}! 👋 Soy el asistente virtual de AutoGest. Puedo ayudarte a:\n\n- Consultar el estado de tu vehículo.\n- Agendar una cita de mantenimiento.\n- Consultar tus citas.\n- Ver el historial de mantenimientos.\n\n¿Qué deseas hacer?";
    }

    private function isGreeting(string $lower): bool
    {
        return Str::contains($lower, ['hola', 'buenos días', 'buenos dias', 'buenas tardes', 'buenas noches']);
    }

    private function resolveUserVehicle($user, string $plate)
    {
        $normalizedPlate = $this->normalizePlate($plate);

        return $user->vehicles()
            ->get()
            ->first(fn (Vehicle $vehicle) => $this->normalizePlate($vehicle->plate) === $normalizedPlate);
    }

    private function normalizePlate(string $plate): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/i', '', $plate) ?? '');
    }

    private function looksLikeVehicleQuery(string $lower, string $text): bool
    {
        if ($this->extractPlate($text) === null) {
            return false;
        }

        return Str::contains($lower, [
            'estado',
            'placa',
            'vehículo',
            'vehiculo',
            'auto',
            'carro',
            'coche',
            'consulta',
            'consultar',
            'revisa',
            'revisar',
            'como esta',
            'cómo está',
            'como va',
            'cómo va',
            'mi auto',
            'mi carro',
            'mi coche',
            'mi vehiculo',
            'mi vehículo',
        ]);
    }

    private function isVehicleStatusRequest(string $lower): bool
    {
        return Str::contains($lower, 'estado')
            && Str::contains($lower, ['auto', 'vehículo', 'vehiculo', 'carro', 'coche']);
    }

    private function isGeneralVehicleInquiry(string $lower): bool
    {
        return Str::contains($lower, [
            'mi auto',
            'mi carro',
            'mi coche',
            'mi vehiculo',
            'mi vehículo',
            'mis autos',
            'mis coches',
            'mis carros',
            'mis vehículos',
            'mis vehiculos',
            'vehículo',
            'vehiculo',
            'auto',
            'carro',
            'coche',
        ]);
    }
}
