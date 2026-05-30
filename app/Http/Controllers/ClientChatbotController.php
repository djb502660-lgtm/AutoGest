<?php

namespace App\Http\Controllers;

use App\Models\ChatbotFaq;
use App\Models\ChatbotMessage;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientChatbotController extends Controller
{
    public function index(Request $request)
    {
        $messages = ChatbotMessage::where('user_id', $request->user()->id)
            ->orderBy('created_at')
            ->take(50)
            ->get();

        $faqs = ChatbotFaq::where('is_active', true)->orderBy('sort_order')->get();

        return view('client.chatbot.index', compact('messages', 'faqs'));
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

        if (Str::contains($lower, ['estado', 'placa', 'vehículo', 'vehiculo', 'como esta', 'cómo está'])) {
            $plate = $this->extractPlate($text);
            if ($plate) {
                $vehicle = $user->vehicles()->where('plate', 'like', "%{$plate}%")->first();
                if ($vehicle) {
                    $order = $vehicle->serviceOrders()->latest()->first();

                    return "Tu {$vehicle->brand} {$vehicle->model} ({$vehicle->plate}) está {$vehicle->statusLabel()}."
                        .($order ? " Última orden: {$order->description} — {$order->statusLabel()}." : '');
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

        if (Str::contains($lower, ['proximo', 'próximo', 'programad', 'cita', 'mantenimiento'])) {
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

        return 'Puedo ayudarte con el estado de tu vehículo, próximos mantenimientos, gastos y preguntas frecuentes. ¿Qué necesitas saber?';
    }

    private function extractPlate(string $text): ?string
    {
        if (preg_match('/[A-Z]{2,3}[-\s]?\d{2,4}/i', $text, $matches)) {
            return strtoupper(str_replace(' ', '-', $matches[0]));
        }

        return null;
    }
}
