<?php

namespace App\Services;

use App\Jobs\NotifyAdvisorsOfChatbotQuery;
use App\Models\AppointmentRequest;
use App\Models\ChatbotFaq;
use App\Models\ChatbotMessage;
use App\Models\Maintenance;
use App\Models\ServiceOrder;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ChatbotService
{
    private const CONTEXT_KEY = 'chatbot_context';

    public function __construct(
        private ChatbotAppointmentService $appointments,
    ) {}

    public function processMessage($user, string $message): string
    {
        $normalized = $this->normalize($message);
        $trimmed = trim($message);

        // Atajos numéricos (compatibilidad)
        if (in_array($trimmed, ['1', '1️⃣'], true)) {
            return $this->vehicleStatus($user);
        }
        if (in_array($trimmed, ['2', '2️⃣'], true)) {
            return $this->handleAppointment($user, 'agendar cita');
        }
        if (in_array($trimmed, ['3', '3️⃣'], true)) {
            return $this->expenseSummary($user);
        }

        // Flujo de citas (agendar, consultar, editar, cancelar)
        if ($this->appointments->shouldHandle($message)) {
            return $this->handleAppointment($user, $message);
        }

        // Seguimiento de contexto conversacional
        if ($contextReply = $this->handleContextFollowUp($user, $message, $normalized)) {
            return $contextReply;
        }

        // Saludos inteligentes
        if ($this->isGreeting($normalized)) {
            return $this->greeting($user);
        }

        // Síntomas mecánicos (diagnóstico guiado)
        if ($symptomReply = $this->handleSymptom($user, $message, $normalized)) {
            return $symptomReply;
        }

        // Estado del vehículo
        if ($this->isVehicleStatusQuery($normalized)) {
            return $this->vehicleStatus($user);
        }

        // Gastos e historial
        if ($this->isExpenseQuery($normalized)) {
            return $this->expenseSummary($user);
        }

        // Órdenes de trabajo activas
        if ($this->isOrderQuery($normalized)) {
            return $this->orderStatus($user);
        }

        // Placa en el mensaje
        if ($plate = $this->extractPlate($message)) {
            return $this->vehicleByPlate($user, $plate);
        }

        // FAQs de la base de datos
        if ($faqAnswer = $this->searchFaq($normalized)) {
            $this->setContext(['last_topic' => 'faq']);

            return $faqAnswer;
        }

        // Consulta abierta con IA
        if ($aiReply = $this->askAI($user, $message)) {
            $this->setContext(['last_topic' => 'ai']);

            return $aiReply;
        }

        // Escalar a asesor humano
        if ($user) {
            NotifyAdvisorsOfChatbotQuery::dispatch($user, $message);
        }

        return 'No encontré una respuesta directa para eso. Un asesor de servicio revisará tu consulta y te contactará pronto.';
    }

    // ─────────────────────────────────────────────────────────────
    // SALUDO INTELIGENTE
    // ─────────────────────────────────────────────────────────────

    private function greeting($user): string
    {
        $nombre = $user ? ' '.explode(' ', trim($user->name))[0] : '';

        $intro = "¡Hola{$nombre}! 👋 Me alegra verte.\n\n"
            ."Soy el asistente inteligente de **AutoGest** y estoy aquí para ayudarte con todo lo relacionado con tu vehículo.\n\n";

        if (! $user) {
            return $intro."Puedes escribirme lo que necesites: consultas de mecánica, estado de tu vehículo o agendar una cita.\n\n"
                .'(Inicia sesión para acceder a la información de tus vehículos.)';
        }

        $vehicles = $user->vehicles()->get();

        if ($vehicles->isEmpty()) {
            return $intro."Aún no tienes vehículos registrados. Contacta al taller para registrarlos.\n\n"
                .'Mientras tanto, puedo responder consultas generales de mecánica.';
        }

        if ($vehicles->count() === 1) {
            $v = $vehicles->first();
            $intro .= "Veo que tienes registrado:\n\n"
                ."🚗 {$v->brand} {$v->model} {$v->year}\n"
                ."Placa: {$v->plate}\n\n";
        } else {
            $lista = $vehicles->map(fn (Vehicle $v) => "🚗 {$v->plate} — {$v->brand} {$v->model}")->join("\n");
            $intro .= "Veo que tienes registrados:\n\n{$lista}\n\n";
        }

        return $intro."¿En qué puedo ayudarte hoy?\n\n"
            ."• Consultar el estado del vehículo\n"
            ."• Agendar, consultar o modificar una cita\n"
            ."• Revisar el historial de mantenimientos\n"
            ."• Resolver dudas sobre mecánica\n\n"
            .'También puedes escribirme lo que necesites con tus propias palabras.';
    }

    // ─────────────────────────────────────────────────────────────
    // ESTADO DEL VEHÍCULO
    // ─────────────────────────────────────────────────────────────

    private function vehicleStatus($user): string
    {
        if (! $user) {
            return '🔒 Debes iniciar sesión para consultar el estado de tus vehículos.';
        }

        $vehicles = $user->vehicles()->with([
            'serviceOrders' => fn ($q) => $q->with('mechanic')->latest()->limit(1),
            'maintenances' => fn ($q) => $q->latest()->limit(3),
        ])->get();

        if ($vehicles->isEmpty()) {
            return '🚗 No tienes vehículos registrados actualmente en AutoGest.';
        }

        if ($vehicles->count() === 1) {
            $reply = $this->buildVehicleStatusReply($vehicles->first(), detailed: true);
            $this->setContext(['last_topic' => 'vehicle_status', 'vehicle_id' => $vehicles->first()->id]);

            return $reply;
        }

        $lines = $vehicles->map(function (Vehicle $v) {
            $order = $v->serviceOrders->first();
            $status = $this->vehicleStatusLabel($v);
            $line = "• {$v->brand} {$v->model} ({$v->plate}) está {$status}";

            if ($order && in_array($order->status, ['recibida', 'en_proceso'], true)) {
                $line .= " — {$order->description} ({$order->statusLabel()})";
            }

            return $line;
        })->join("\n");

        $this->setContext(['last_topic' => 'vehicle_list']);

        return "Tienes {$vehicles->count()} vehículos registrados:\n\n{$lines}\n\n"
            .'Indícame la placa del vehículo que deseas consultar con más detalle.';
    }

    private function vehicleByPlate($user, string $plate): string
    {
        if (! $user) {
            return '🔒 Inicia sesión para consultar el estado de tu vehículo.';
        }

        $vehicle = $user->vehicles->first(function ($v) use ($plate) {
            return strtoupper(preg_replace('/[^A-Z0-9]/i', '', $v->plate)) === $plate;
        });

        if (! $vehicle) {
            return "❌ No encontré la placa **{$plate}** asociada a tu cuenta. ¿Deseas consultar otra placa?";
        }

        $vehicle->load(['serviceOrders' => fn ($q) => $q->with('mechanic')->latest()->limit(1)]);
        $reply = $this->buildVehicleStatusReply($vehicle, detailed: true);
        $this->setContext(['last_topic' => 'vehicle_status', 'vehicle_id' => $vehicle->id]);

        return $reply;
    }

    private function buildVehicleStatusReply(Vehicle $vehicle, bool $detailed = false): string
    {
        $order = $vehicle->serviceOrders->first();
        $status = $this->vehicleStatusLabel($vehicle);

        // Formato compatible con tests existentes
        $summary = "Tu {$vehicle->brand} {$vehicle->model} ({$vehicle->plate}) está {$status}.";

        if ($order) {
            $summary .= " Última orden: {$order->description} — {$order->statusLabel()}.";
        }

        if (! $detailed || ! $order || ! in_array($order->status, ['recibida', 'en_proceso'], true)) {
            return $summary;
        }

        $detail = "\n\nRevisando la información...\n\n"
            ."Tu vehículo {$vehicle->plate} se encuentra actualmente:\n\n"
            ."🔧 **{$order->statusLabel()}**\n";

        if ($order->progress !== null) {
            $detail .= "Progreso: **{$order->progress} %**\n";
        }

        if ($order->mechanic) {
            $detail .= "\nMecánico asignado: **{$order->mechanic->name}**.\n";
        }

        $completed = Maintenance::query()
            ->where('service_order_id', $order->id)
            ->where('status', 'completado')
            ->pluck('type')
            ->take(5);

        if ($completed->isNotEmpty()) {
            $detail .= "\nTrabajo realizado:\n".$completed->map(fn ($t) => "• {$t}")->join("\n");
        }

        $pending = Maintenance::query()
            ->where('service_order_id', $order->id)
            ->whereIn('status', ['pendiente', 'en_proceso'])
            ->pluck('type')
            ->take(5);

        if ($pending->isNotEmpty()) {
            $detail .= "\n\nTrabajo pendiente:\n".$pending->map(fn ($t) => "• {$t}")->join("\n");
        }

        if ($order->completed_at) {
            $detail .= "\n\nFecha estimada de entrega: **{$order->completed_at->format('d/m/Y H:i')}**.";
        }

        return $summary.$detail;
    }

    private function vehicleStatusLabel(Vehicle $vehicle): string
    {
        return method_exists($vehicle, 'statusLabel') ? $vehicle->statusLabel() : ucfirst($vehicle->status ?? 'Activo');
    }

    // ─────────────────────────────────────────────────────────────
    // ÓRDENES Y GASTOS
    // ─────────────────────────────────────────────────────────────

    private function orderStatus($user): string
    {
        if (! $user) {
            return '🔒 Inicia sesión para ver tus órdenes de servicio.';
        }

        $orders = ServiceOrder::where('client_id', $user->id)
            ->with(['vehicle', 'mechanic'])
            ->whereIn('status', ['recibida', 'en_proceso'])
            ->latest()
            ->take(3)
            ->get();

        if ($orders->isEmpty()) {
            return '📋 No tienes órdenes de servicio activas en este momento.';
        }

        $lista = $orders->map(function ($o) {
            $line = "• **{$o->order_number}** – {$o->vehicle?->plate}: {$o->statusLabel()} ({$o->progress}%)";
            if ($o->mechanic) {
                $line .= " — Mecánico: {$o->mechanic->name}";
            }

            return $line;
        })->join("\n");

        $this->setContext(['last_topic' => 'orders']);

        return "📋 **Tus órdenes activas:**\n\n{$lista}";
    }

    private function expenseSummary($user): string
    {
        if (! $user) {
            return '🔒 Inicia sesión para ver tu resumen de gastos.';
        }

        $year = (int) now()->year;
        $startOfYear = Carbon::create($year, 1, 1)->startOfDay();

        $maintenances = Maintenance::query()
            ->whereHas('vehicle', fn ($q) => $q->where('client_id', $user->id))
            ->where('status', 'completado')
            ->where('performed_at', '>=', $startOfYear)
            ->get();

        $count = $maintenances->count();
        $total = $maintenances->sum('cost');

        if ($count === 0) {
            $total = ServiceOrder::where('client_id', $user->id)
                ->whereIn('status', ['completada', 'entregada'])
                ->where('completed_at', '>=', $startOfYear)
                ->sum('total_cost');

            $count = ServiceOrder::where('client_id', $user->id)
                ->whereIn('status', ['completada', 'entregada'])
                ->where('completed_at', '>=', $startOfYear)
                ->count();
        }

        if ($count === 0) {
            return '💰 Aún no tienes servicios completados registrados este año en AutoGest.';
        }

        $mostExpensive = $maintenances->sortByDesc('cost')->first();
        $expensiveLine = '';

        if ($mostExpensive && $mostExpensive->cost > 0) {
            $label = $mostExpensive->type ?: $mostExpensive->description;
            $expensiveLine = "\n\nTu servicio más costoso fue:\n**{$label}** ($".number_format((float) $mostExpensive->cost, 2).')';
        }

        $this->setContext(['last_topic' => 'expenses']);

        return "Revisando tu historial...\n\n"
            ."Durante este año has realizado:\n\n"
            ."✔️ **{$count}** mantenimientos.\n"
            .'💲 Total invertido: **$'.number_format((float) $total, 2)."**"
            .$expensiveLine
            ."\n\nSi deseas, puedo mostrarte el detalle de cada servicio o ayudarte a agendar uno nuevo.";
    }

    // ─────────────────────────────────────────────────────────────
    // DIAGNÓSTICO GUIADO DE SÍNTOMAS
    // ─────────────────────────────────────────────────────────────

    private function handleSymptom($user, string $message, string $normalized): ?string
    {
        $context = session(self::CONTEXT_KEY, []);

        if (($context['symptom_flow'] ?? null) === 'brake_noise') {
            return $this->continueBrakeNoiseFlow($user, $message, $normalized, $context);
        }

        if (Str::contains($normalized, ['ruido', 'rechin', 'chirr', 'grit']) && Str::contains($normalized, ['fren', 'freno'])) {
            $this->setContext(['symptom_flow' => 'brake_noise', 'symptom' => 'ruido al frenar']);

            return "Entiendo. Voy a ayudarte a identificar el posible problema.\n\n"
                ."¿El ruido ocurre:\n\n"
                ."🔹 Siempre que frenas?\n"
                ."🔹 Solo cuando el vehículo está frío?\n"
                ."🔹 O únicamente a altas velocidades?\n\n"
                .'Mientras más detalles me des, mejor podré orientarte.';
        }

        if (Str::contains($normalized, ['consume mucha gasolina', 'gasta mucha gasolina', 'mucho combustible', 'bajo rendimiento'])) {
            $this->setContext(['symptom_flow' => 'fuel_consumption', 'last_topic' => 'fuel']);

            return "Ese problema puede deberse a varias causas.\n\n"
                ."Las más comunes son:\n\n"
                ."• Filtro de aire obstruido\n"
                ."• Bujías desgastadas\n"
                ."• Inyectores sucios\n"
                ."• Presión incorrecta de las llantas\n"
                ."• Sensor de oxígeno defectuoso\n\n"
                ."Para darte una recomendación más precisa, ¿se encendió la luz de **Check Engine**?\n\n"
                .'También puedo agendar una revisión de diagnóstico en el taller.';
        }

        if (Str::contains($normalized, ['cambiar las llantas', 'cambiar llantas', 'debo cambiar llantas', 'cuando cambiar llantas'])) {
            $this->setContext(['symptom_flow' => 'tire_check', 'last_topic' => 'tires']);

            return "Puedo ayudarte a determinarlo.\n\n"
                ."¿Aproximadamente cuántos kilómetros tienen las llantas actuales?\n\n"
                .'¿Has notado desgaste irregular o poca adherencia cuando llueve?';
        }

        if (($context['symptom_flow'] ?? null) === 'tire_check') {
            if (preg_match('/(\d[\d\s\.]*)\s*(mil|km|kilomet)/u', $normalized, $m)) {
                $km = (int) preg_replace('/\D/', '', $m[1]);
                $this->setContext(['symptom_flow' => null, 'last_topic' => 'tires', 'tire_km' => $km]);

                return "Gracias por la información.\n\n"
                    ."Generalmente unas llantas duran entre 40 000 y 60 000 kilómetros, dependiendo del tipo de conducción y del mantenimiento.\n\n"
                    ."Con el kilometraje que me indicas ({$km} km), es recomendable realizar una **inspección** para verificar la profundidad del dibujo y el estado general.\n\n"
                    .'Si lo deseas, puedo agendar una revisión sin compromiso. Solo dime cuándo te conviene.';
            }
        }

        return null;
    }

    private function continueBrakeNoiseFlow($user, string $message, string $normalized, array $context): string
    {
        if (Str::contains($normalized, ['siempre', 'cada vez', 'todo el tiempo'])) {
            $detail = 'siempre que frena';
        } elseif (Str::contains($normalized, ['frio', 'frío', 'arranque', 'calent'])) {
            $detail = 'cuando el vehículo está frío';
        } elseif (Str::contains($normalized, ['veloc', 'rapido', 'rápido', 'autopista', 'carretera'])) {
            $detail = 'a altas velocidades';
        } else {
            return 'Gracias. ¿El ruido ocurre siempre que frenas, cuando está frío o solo a altas velocidades?';
        }

        $this->setContext([
            'symptom_flow' => null,
            'last_topic' => 'brake_noise',
            'symptom_detail' => $detail,
        ]);

        $reply = "Gracias por la información.\n\n"
            ."Ese síntoma ({$detail}) suele estar relacionado con:\n\n"
            ."• Pastillas de freno desgastadas\n"
            ."• Discos de freno deformados\n"
            ."• Componentes sueltos del sistema de frenado\n\n"
            ."Por seguridad, te recomiendo una revisión lo antes posible.\n\n";

        if ($user) {
            $reply .= '¿Deseas que agende una cita para inspeccionar tu vehículo?';
        } else {
            $reply .= 'Inicia sesión si deseas agendar una cita de inspección.';
        }

        return $reply;
    }

    // ─────────────────────────────────────────────────────────────
    // MEMORIA DE CONTEXTO
    // ─────────────────────────────────────────────────────────────

    private function handleContextFollowUp($user, string $message, string $normalized): ?string
    {
        $context = session(self::CONTEXT_KEY, []);

        // Confirmación para agendar tras diagnóstico
        if ($this->isAffirmative($normalized) && in_array($context['last_topic'] ?? '', ['brake_noise', 'tires', 'fuel'], true)) {
            if (! $user) {
                return '🔒 Inicia sesión para que pueda agendar tu cita.';
            }

            $reason = match ($context['last_topic']) {
                'brake_noise' => 'Revisión de frenos por ruido',
                'tires' => 'Inspección de llantas',
                'fuel' => 'Diagnóstico de consumo de combustible',
                default => 'Revisión general',
            };

            session()->forget(self::CONTEXT_KEY);

            return $this->appointments->handle($user, "agendar cita {$reason}");
        }

        // "¿Y cuánto costaría?" u otras preguntas de seguimiento
        if ($this->isCostFollowUp($normalized)) {
            return match ($context['last_topic'] ?? '') {
                'tires' => "Si te refieres a la revisión de las llantas, normalmente la **inspección es gratuita** cuando se realiza junto con un mantenimiento.\n\n"
                    .'Si se requiere reemplazo, el costo dependerá de la marca y la medida de las llantas. '
                    .'Puedo solicitar una cotización para tu vehículo si lo deseas.',
                'brake_noise' => 'La inspección de frenos suele tener un costo moderado; el reemplazo de pastillas o discos depende del modelo de tu vehículo. '
                    .'Un asesor puede darte una cotización exacta tras la revisión.',
                'expenses' => $this->expenseSummary($user),
                default => 'Para darte un costo preciso necesito saber a qué servicio te refieres. '
                    .'¿Es una revisión, un mantenimiento o algún trabajo específico?',
            };
        }

        // "Sí" tras ofrecer agendar cita desde síntoma
        if ($this->isAffirmative($normalized) && ($context['awaiting_appointment'] ?? false) && $user) {
            return $this->appointments->handle($user, 'agendar cita');
        }

        return null;
    }

    private function setContext(array $data): void
    {
        $current = session(self::CONTEXT_KEY, []);
        session([self::CONTEXT_KEY => array_merge($current, $data)]);
    }

    // ─────────────────────────────────────────────────────────────
    // IA CON HISTORIAL
    // ─────────────────────────────────────────────────────────────

    private function askAI($user, string $prompt): ?string
    {
        $apiKey = config('services.openai.key', env('OPENAI_API_KEY'));

        if (! $apiKey || $apiKey === 'tu_sk_live_o_test_key_aqui') {
            return null;
        }

        try {
            $vehicleContext = '';
            if ($user) {
                $vehicles = $user->vehicles()->get();
                if ($vehicles->isNotEmpty()) {
                    $vehicleContext = ' Vehículos del cliente: '.$vehicles->map(
                        fn (Vehicle $v) => "{$v->brand} {$v->model} ({$v->plate})"
                    )->join(', ').'.';
                }
            }

            $systemPrompt = 'Eres AutoGest Bot, el asistente inteligente del taller mecánico AutoGest del ISTAE. '
                .'Atiendes al cliente '.($user?->name ?? 'visitante').'.'.$vehicleContext.' '
                .'Responde en español, de forma amable, concisa y experta sobre mecánica automotriz, '
                .'servicios del taller, diagnósticos orientativos y mantenimiento preventivo. '
                .'Haz preguntas de seguimiento cuando necesites más datos. '
                .'Si el cliente quiere agendar, indícale que puede decir "quiero agendar una cita". '
                .'Si no puedes resolver algo, sugiere contactar a un asesor del taller. '
                .'No inventes precios exactos ni diagnósticos definitivos sin inspección.';

            $messages = [['role' => 'system', 'content' => $systemPrompt]];

            if ($user) {
                $history = ChatbotMessage::where('user_id', $user->id)
                    ->orderByDesc('created_at')
                    ->take(6)
                    ->get()
                    ->reverse();

                foreach ($history as $msg) {
                    $messages[] = [
                        'role' => $msg->sender === 'user' ? 'user' : 'assistant',
                        'content' => $msg->message,
                    ];
                }
            }

            $messages[] = ['role' => 'user', 'content' => $prompt];

            $response = Http::withToken($apiKey)
                ->timeout(12)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => $messages,
                    'max_tokens' => 400,
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                return trim($response->json('choices.0.message.content'));
            }

            Log::warning('[ChatbotService] La API de IA respondió con error.', [
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 500),
            ]);
        } catch (Throwable $e) {
            Log::warning('[ChatbotService] Error al consultar la IA.', [
                'exception' => $e->getMessage(),
            ]);
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────
    // UTILIDADES
    // ─────────────────────────────────────────────────────────────

    private function handleAppointment($user, string $message): string
    {
        if (! $user) {
            return '🔒 Debes iniciar sesión para gestionar citas de servicio.';
        }

        return $this->appointments->handle($user, $message);
    }

    private function searchFaq(string $normalized): ?string
    {
        try {
            $faq = ChatbotFaq::where('is_active', true)->get()->first(function ($item) use ($normalized) {
                $keywords = array_map('trim', explode(',', Str::lower($item->keywords ?? '')));
                foreach ($keywords as $kw) {
                    if (! empty($kw) && Str::contains($normalized, $kw)) {
                        return true;
                    }
                }

                return Str::contains($normalized, Str::lower($item->question));
            });

            return $faq?->answer;
        } catch (Throwable $e) {
            report($e);

            Log::error('[ChatbotService] No se pudieron consultar las FAQs.', [
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function isGreeting(string $normalized): bool
    {
        return (bool) preg_match('/\b(hola|buenas|buenos|buen dia|buen día|saludos|que tal|hey|buen)\b/u', $normalized);
    }

    private function isVehicleStatusQuery(string $normalized): bool
    {
        return Str::contains($normalized, [
            'estado', 'mi auto', 'mi carro', 'mi vehiculo', 'mis autos',
            'como va', 'donde esta', 'donde está', 'consultar el estado',
            'consultar estado', 'estado del vehiculo', 'estado de mi',
        ]);
    }

    private function isExpenseQuery(string $normalized): bool
    {
        return Str::contains($normalized, [
            'gastos', 'cuanto he pagado', 'cuanto gaste', 'cuánto he gastado',
            'historial de pago', 'mis pagos', 'costo', 'invertido', 'gastado',
        ]);
    }

    private function isOrderQuery(string $normalized): bool
    {
        return Str::contains($normalized, [
            'orden de servicio', 'ordenes activas', 'mis ordenes', 'trabajo en el taller',
        ]);
    }

    private function isCostFollowUp(string $normalized): bool
    {
        return Str::contains($normalized, [
            'cuanto cost', 'cuánto cost', 'que precio', 'qué precio',
            'cuanto sale', 'cuánto sale', 'cuanto cobr', 'cuánto cobr',
            'y el costo', 'y cuanto', 'y cuánto',
        ]);
    }

    private function isAffirmative(string $normalized): bool
    {
        return (bool) preg_match('/^(si|sí|ok|vale|claro|de acuerdo|por supuesto|agenda|agendar)\b/u', $normalized)
            || in_array(trim($normalized), ['si', 'sí', 'sí.', 'si.'], true);
    }

    private function normalize(string $text): string
    {
        $text = Str::lower(trim($text));
        $map = [
            '/[áàâãä]/u' => 'a', '/[éèêë]/u' => 'e',
            '/[íìîï]/u' => 'i', '/[óòôõö]/u' => 'o',
            '/[úùûü]/u' => 'u', '/[ñ]/u' => 'n',
        ];

        return preg_replace(array_keys($map), array_values($map), $text);
    }

    private function extractPlate(string $text): ?string
    {
        if (preg_match('/\b([A-Z]{2,3}[-\s]?\d{2,4}[A-Z]?)\b/i', $text, $matches)) {
            return strtoupper(preg_replace('/[^A-Z0-9]/i', '', $matches[1]));
        }

        return null;
    }
}
