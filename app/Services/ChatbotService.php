<?php

namespace App\Services;

use App\Jobs\NotifyAdvisorsOfChatbotQuery;
use App\Models\Vehicle;
use Illuminate\Support\Str;

class ChatbotService
{
    private const CONTEXT_KEY = 'chatbot_context';

    private $appointments;

    private $vehicleService;

    private $serviceOrderService;

    private $maintenanceService;

    public function __construct(
        ChatbotAppointmentService $appointments,
        VehicleService $vehicleService,
        ServiceOrderService $serviceOrderService,
        MaintenanceService $maintenanceService
    ) {
        $this->appointments = $appointments;
        $this->vehicleService = $vehicleService;
        $this->serviceOrderService = $serviceOrderService;
        $this->maintenanceService = $maintenanceService;
    }

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

        // Escalar a asesor humano (funciones limitadas según decisión de diseño)
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
            return $intro."Puedes escribirme lo que necesites: estado de tu vehículo o agendar una cita.\n\n"
                .'(Inicia sesión para acceder a la información de tus vehículos.)';
        }

        $vehicles = $this->vehicleService->getClientVehicles($user->id);

        if ($vehicles->isEmpty()) {
            return $intro.'Aún no tienes vehículos registrados. Contacta al taller para registrarlos.';
        }

        if ($vehicles->count() === 1) {
            $v = $vehicles->first();
            $intro .= "Veo que tienes registrado:\n\n"
                ."🚗 {$v->brand} {$v->model} {$v->year}\n"
                ."Placa: {$v->plate}\n\n";
        } else {
            $lista = $vehicles->map(fn ($v) => "🚗 {$v->plate} — {$v->brand} {$v->model}")->join("\n");
            $intro .= "Veo que tienes registrados:\n\n{$lista}\n\n";
        }

        return $intro."¿En qué puedo ayudarte hoy?\n\n"
            ."• Consultar el estado del vehículo\n"
            ."• Agendar, consultar o modificar una cita\n"
            ."• Revisar el historial de mantenimientos\n\n"
            .'Escribe lo que necesites con tus propias palabras.';
    }

    // ─────────────────────────────────────────────────────────────
    // ESTADO DEL VEHÍCULO
    // ─────────────────────────────────────────────────────────────

    private function vehicleStatus($user): string
    {
        if (! $user) {
            return '🔒 Debes iniciar sesión para consultar el estado de tus vehículos.';
        }

        $vehicles = $this->vehicleService->getClientVehicles($user->id);

        if ($vehicles->isEmpty()) {
            return '🚗 No tienes vehículos registrados actualmente en AutoGest.';
        }

        $vehicles = $vehicles->load([
            'serviceOrders' => fn ($q) => $q->with('mechanic')->latest()->limit(1),
            'maintenances' => fn ($q) => $q->latest()->limit(3),
        ]);

        if ($vehicles->count() === 1) {
            $reply = $this->buildVehicleStatusReply($vehicles->first(), detailed: false);
            $this->setContext(['last_topic' => 'vehicle_status', 'vehicle_id' => $vehicles->first()->id]);

            return $reply;
        }

        $lines = $vehicles->map(function ($v) {
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
        $reply = $this->buildVehicleStatusReply($vehicle, detailed: false);
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

        $maintenanceSummary = $this->maintenanceService->getOrderMaintenancesSummary($order->id);

        if ($maintenanceSummary['completed']->isNotEmpty()) {
            $detail .= "\nTrabajo realizado:\n".$maintenanceSummary['completed']->map(fn ($t) => "• {$t}")->join("\n");
        }

        if ($maintenanceSummary['pending']->isNotEmpty()) {
            $detail .= "\n\nTrabajo pendiente:\n".$maintenanceSummary['pending']->map(fn ($t) => "• {$t}")->join("\n");
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

        $orders = $this->serviceOrderService->getClientOrders($user->id)
            ->whereIn('status', ['recibida', 'en_proceso'])
            ->with(['vehicle', 'mechanic'])
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

        $summary = $this->maintenanceService->getClientExpensesSummary($user->id);

        if ($summary['count'] === 0) {
            return '💰 Aún no tienes servicios completados registrados este año en AutoGest.';
        }

        $mostExpensive = $summary['most_expensive'];
        $expensiveLine = '';

        if ($mostExpensive && $mostExpensive->cost > 0) {
            $label = $mostExpensive->type ?: $mostExpensive->description;
            $expensiveLine = "\n\nTu servicio más costoso fue:\n**{$label}** ($".number_format((float) $mostExpensive->cost, 2).')';
        }

        $this->setContext(['last_topic' => 'expenses']);

        return "Revisando tu historial...\n\n"
            ."Durante este año has realizado:\n\n"
            ."✔️ **{$summary['count']}** mantenimientos.\n"
            .'💲 Total invertido: **$'.number_format((float) $summary['total'], 2).'**'
            .$expensiveLine
            ."\n\nSi deseas, puedo mostrarte el detalle de cada servicio o ayudarte a agendar uno nuevo.";
    }

    // ─────────────────────────────────────────────────────────────
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
    // UTILIDADES
    // ─────────────────────────────────────────────────────────────

    private function handleAppointment($user, string $message): string
    {
        if (! $user) {
            return '🔒 Debes iniciar sesión para gestionar citas de servicio.';
        }

        return $this->appointments->handle($user, $message);
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
        return str()->contains($normalized, [
            'gastos', 'cuanto he pagado', 'cuanto gaste', 'cuánto he gastado',
            'historial de pago', 'mis pagos', 'costo', 'invertido', 'gastado',
        ]);
    }

    private function isOrderQuery(string $normalized): bool
    {
        return str()->contains($normalized, [
            'orden de servicio', 'ordenes activas', 'mis ordenes', 'trabajo en el taller',
        ]);
    }

    private function isCostFollowUp(string $normalized): bool
    {
        return str()->contains($normalized, [
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
        $text = mb_strtolower(trim($text), 'UTF-8');
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
