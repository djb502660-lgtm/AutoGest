<?php

namespace App\Services;

use App\Jobs\NotifyAdvisorsOfChatbotQuery;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\VehiclePlate;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ChatbotService
{
    private const CONTEXT_KEY = 'chatbot_context';

    public function __construct(
        private ChatbotAppointmentService $appointments,
        private VehicleService $vehicleService,
        private ServiceOrderService $serviceOrderService,
        private MaintenanceService $maintenanceService,
        private ChatbotFaqService $faqs,
    ) {}

    public function processMessage(?User $user, string $message): string
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

        return match ($this->classifyIntent($normalized, $message)) {
            'hours' => $this->hoursAnswer(),
            'services' => $this->servicesAnswer(),
            'vehicle' => $this->vehicleStatus($user),
            'expenses' => $this->expenseSummary($user),
            'orders' => $this->orderStatus($user),
            'plate' => $this->vehicleByPlate($user, (string) $this->extractPlate($message), $message),
            'greeting' => $this->greeting($user),
            'farewell' => $this->farewell($user),
            'thanks' => $this->thanks($user),
            'help' => $this->helpMenu(),
            'datetime' => $this->datetimeClarify($message),
            default => $this->faqs->answerFor($normalized) ?? $this->escalateToAdvisor($user, $message),
        };
    }

    private function classifyIntent(string $normalized, string $original): string
    {
        if ($this->isHoursQuery($normalized)) {
            return 'hours';
        }

        if ($this->isVehicleStatusQuery($normalized)) {
            return 'vehicle';
        }

        if ($this->isExpenseQuery($normalized)) {
            return 'expenses';
        }

        if ($this->isOrderQuery($normalized)) {
            return 'orders';
        }

        if ($this->isServicesCatalogQuery($normalized)) {
            return 'services';
        }

        if ($this->isHelpRequest($normalized)) {
            return 'help';
        }

        if ($this->appointments->looksLikeScheduleFragment($original)) {
            return 'datetime';
        }

        if ($this->extractPlate($original)) {
            return 'plate';
        }

        if ($this->isGreeting($normalized)) {
            return 'greeting';
        }

        if ($this->isFarewell($normalized)) {
            return 'farewell';
        }

        if ($this->isThanks($normalized)) {
            return 'thanks';
        }

        return 'unknown';
    }

    private function escalateToAdvisor(?User $user, string $message): string
    {
        if ($user) {
            NotifyAdvisorsOfChatbotQuery::dispatch($user, $message);
        }

        return 'No encontré una respuesta directa para eso. Un asesor de servicio revisará tu consulta y te contactará pronto.';
    }

    // ─────────────────────────────────────────────────────────────
    // SALUDO INTELIGENTE
    // ─────────────────────────────────────────────────────────────

    private function greeting(?User $user): string
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
            return $intro.'Aún no tienes vehículos registrados. Escribe **agendar cita** y la placa para registrar uno nuevo y pedir el turno.';
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

        return $intro."¿En qué puedo ayudarte hoy?\n\n".$this->capabilityMenu();
    }

    private function farewell(?User $user): string
    {
        $nombre = $user ? ' '.explode(' ', trim($user->name))[0] : '';

        return "¡Hasta luego{$nombre}! Cuando quieras, aquí estaré para ayudarte con tu vehículo.";
    }

    private function thanks(?User $user): string
    {
        $nombre = $user ? ' '.explode(' ', trim($user->name))[0] : '';

        return "¡Con gusto{$nombre}! Si necesitas el estado de tu vehículo, una cita o el historial de gastos, dímelo.";
    }

    private function helpMenu(): string
    {
        return "Puedo ayudarte con esto:\n\n".$this->capabilityMenu();
    }

    private function capabilityMenu(): string
    {
        return "• Consultar la información y el estado de tu vehículo\n"
            ."• Agendar, consultar, editar o cancelar una cita\n"
            ."• Revisar el historial de mantenimientos\n\n"
            .'Escribe lo que necesites con tus propias palabras.';
    }

    // ─────────────────────────────────────────────────────────────
    // ESTADO DEL VEHÍCULO
    // ─────────────────────────────────────────────────────────────

    private function vehicleStatus(?User $user): string
    {
        if (! $user) {
            return '🔒 Debes iniciar sesión para consultar el estado de tus vehículos.';
        }

        $vehicles = $this->vehicleService->getClientVehicles($user->id);

        if ($vehicles->isEmpty()) {
            return '🚗 No tienes vehículos registrados. Escribe **agendar cita** y la placa para registrar uno nuevo.';
        }

        $vehicles = $vehicles->load([
            'serviceOrders' => fn ($q) => $q->with('mechanic')->latest()->limit(1),
            'maintenances' => fn ($q) => $q->latest()->limit(3),
            'appointmentRequests' => fn ($q) => $q->whereIn('status', ['pendiente', 'confirmada'])->orderBy('requested_date')->limit(1),
        ]);

        if ($vehicles->count() === 1) {
            $reply = $this->buildVehicleStatusReply($vehicles->first(), detailed: true);
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

    private function vehicleByPlate(?User $user, string $plate, string $original = ''): string
    {
        if (! $user) {
            return '🔒 Inicia sesión para consultar el estado de tu vehículo.';
        }

        $vehicle = $user->vehicles->first(function ($v) use ($plate) {
            return strtoupper(preg_replace('/[^A-Z0-9]/i', '', $v->plate)) === $plate;
        });

        if (! $vehicle) {
            $display = VehiclePlate::display($original) ?? VehiclePlate::formatForStorage($plate);

            if (VehiclePlate::isStandalone(trim($original))) {
                return $this->appointments->handle($user, 'agendar cita '.$display);
            }

            return "No encontré la placa **{$display}** en tu cuenta. Si quieres agendar una cita con ese vehículo, escribe **agendar {$display}** y lo registramos.";
        }

        $vehicle->load([
            'serviceOrders' => fn ($q) => $q->with('mechanic')->latest()->limit(1),
            'maintenances' => fn ($q) => $q->latest()->limit(3),
            'appointmentRequests' => fn ($q) => $q->whereIn('status', ['pendiente', 'confirmada'])->orderBy('requested_date')->limit(1),
        ]);
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

        $facts = [];
        if ($vehicle->year) {
            $facts[] = "Año: {$vehicle->year}";
        }
        if ($vehicle->color) {
            $facts[] = "Color: {$vehicle->color}";
        }
        if ($vehicle->mileage !== null && $vehicle->mileage !== '') {
            $facts[] = 'Kilometraje: '.number_format((int) $vehicle->mileage).' km';
        }

        $info = $summary;
        if ($facts !== []) {
            $info .= "\n\n".collect($facts)->map(fn (string $fact) => "• {$fact}")->join("\n");
        }

        $appointment = $vehicle->appointmentRequests
            ->first(fn ($item) => in_array($item->status, ['pendiente', 'confirmada'], true));
        if ($appointment) {
            $time = $appointment->requested_time
                ? Carbon::parse($appointment->requested_time)->format('g:i A')
                : '—';
            $info .= "\n\nPróxima cita: {$appointment->requested_date->format('d/m/Y')} a las {$time} ({$appointment->service_type}).";
        }

        $lastMaintenance = $vehicle->maintenances->first();
        if ($lastMaintenance) {
            $label = $lastMaintenance->type ?: $lastMaintenance->description;
            $when = optional($lastMaintenance->performed_at ?? $lastMaintenance->created_at)->format('d/m/Y');
            $info .= "\nÚltimo mantenimiento: {$label}".($when ? " ({$when})" : '').'.';
        }

        if (! $detailed || ! $order || ! in_array($order->status, ['recibida', 'en_proceso'], true)) {
            return $info;
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

        return $info.$detail;
    }

    private function vehicleStatusLabel(Vehicle $vehicle): string
    {
        return method_exists($vehicle, 'statusLabel') ? $vehicle->statusLabel() : ucfirst($vehicle->status ?? 'Activo');
    }

    // ─────────────────────────────────────────────────────────────
    // ÓRDENES Y GASTOS
    // ─────────────────────────────────────────────────────────────

    private function orderStatus(?User $user): string
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

    private function expenseSummary(?User $user): string
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

    private function handleContextFollowUp(?User $user, string $message, string $normalized): ?string
    {
        $context = session(self::CONTEXT_KEY, []);

        if (($context['last_topic'] ?? '') === 'vehicle_list' && $user) {
            $matched = $this->appointments->matchOwnedVehicle($user, $message);
            if ($matched) {
                $plate = (string) ($this->extractPlate($matched->plate) ?: strtoupper((string) preg_replace('/[^A-Z0-9]/i', '', $matched->plate)));

                return $this->vehicleByPlate($user, $plate, $matched->plate);
            }
        }

        if (($context['last_topic'] ?? '') === 'datetime_hint') {
            if ($this->isAffirmative($normalized)) {
                $hint = trim((string) ($context['datetime_text'] ?? ''));
                session()->forget(self::CONTEXT_KEY);

                if (! $user) {
                    return '🔒 Inicia sesión para que pueda agendar tu cita.';
                }

                return $this->appointments->handle($user, 'agendar cita'.($hint !== '' ? " {$hint}" : ''));
            }

            if ($this->isNegative($normalized)) {
                session()->forget(self::CONTEXT_KEY);

                return 'De acuerdo. Puedo ayudarte con el estado de tu vehículo, una cita o el historial de gastos.';
            }
        }

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

    private function handleAppointment(?User $user, string $message): string
    {
        if (! $user) {
            return '🔒 Debes iniciar sesión para gestionar citas de servicio.';
        }

        return $this->appointments->handle($user, $message);
    }

    private function isGreeting(string $normalized): bool
    {
        return $this->isPureConversational($normalized, [
            'hola', 'holaa', 'holas', 'holi', 'holii',
            'buenas', 'buenos', 'buen',
            'saludos', 'saludo',
            'hey', 'hi', 'hello', 'ey',
            'klk', 'qlk', 'qtlk', 'klkk',
            'habla', 'wena', 'wenas',
            'alo', 'aloh',
        ], [
            'buen dia', 'buenos dias', 'buenas tardes', 'buenas noches',
            'que tal', 'que mas', 'que hay', 'que hubo',
            'que lo que', 'que lo q', 'que xopa',
            'como estas', 'como esta', 'como te va', 'como andas',
        ], [
            'dias', 'dia', 'tardes', 'noches', 'noche',
            'tal', 'mas', 'hay', 'hubo', 'quiubo', 'xopa',
            'como', 'estas', 'esta', 'andas', 'lo',
        ]);
    }

    private function isFarewell(string $normalized): bool
    {
        return $this->isPureConversational($normalized, [
            'adios', 'chao', 'chau', 'bye',
        ], [
            'hasta luego', 'nos vemos', 'hasta pronto', 'cuidate',
            'que te vaya bien', 'me despido',
        ]);
    }

    private function isThanks(string $normalized): bool
    {
        return $this->isPureConversational($normalized, [
            'gracias', 'grasias', 'thanks', 'thx',
        ], [
            'mil gracias', 'muchas gracias', 'te agradezco', 'muy amable',
            'se agradece',
        ]);
    }

    private function isHelpRequest(string $normalized): bool
    {
        return $this->isPureConversational($normalized, [
            'ayuda', 'help', 'menu', 'opciones',
        ], [
            'que puedes hacer', 'que sabes hacer', 'en que me ayudas',
            'que haces', 'como funciona', 'que opciones hay',
        ]);
    }

    /**
     * True si el mensaje es solo small talk (saludo/despedida/gracias/ayuda),
     * sin palabras de negocio. Así «habla klk» saluda y «habla con un asesor» no.
     *
     * @param  list<string>  $strongTokens
     * @param  list<string>  $phrases
     * @param  list<string>  $weakTokens
     */
    private function isPureConversational(string $normalized, array $strongTokens, array $phrases, array $weakTokens = []): bool
    {
        $text = trim(preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $normalized) ?? $normalized);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        if ($text === '') {
            return false;
        }

        $allowed = array_merge($strongTokens, $weakTokens);

        foreach ($phrases as $phrase) {
            if ($text === $phrase || str_starts_with($text, $phrase.' ') || str_ends_with($text, ' '.$phrase)) {
                $remainder = trim(preg_replace('/\b'.preg_quote($phrase, '/').'\b/u', '', $text) ?? '');
                $remainder = trim(preg_replace('/\s+/u', ' ', $remainder) ?? $remainder);
                if ($remainder === '' || $this->tokensAreConversationalFillers($remainder, $allowed)) {
                    return true;
                }
            }
        }

        return $this->tokensAreConversationalFillers($text, $allowed)
            && $this->containsAnyToken($text, $strongTokens);
    }

    /**
     * @param  list<string>  $intentTokens
     */
    private function tokensAreConversationalFillers(string $text, array $intentTokens): bool
    {
        $fillers = [
            'a', 'de', 'el', 'la', 'los', 'las', 'un', 'una', 'y', 'o', 'que', 'te', 'me',
            'mi', 'tu', 'por', 'favor', 'please', 'eh', 'pues', 'ya', 'ok', 'vale',
            'bro', 'pana', 'socio', 'amigo', 'parce', 'man', 'men', 'wey', 'q',
        ];
        $allowed = array_flip(array_merge($intentTokens, $fillers));

        foreach (preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) as $token) {
            if (! isset($allowed[$token])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string>  $tokens
     */
    private function containsAnyToken(string $text, array $tokens): bool
    {
        $present = array_flip(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        foreach ($tokens as $token) {
            if (isset($present[$token])) {
                return true;
            }
        }

        return false;
    }

    private function hoursAnswer(): string
    {
        return $this->faqs->answerForIntent('hours')
            ?? 'Atendemos de lunes a viernes de 8:00 a 18:00 y sábados de 8:00 a 13:00.';
    }

    private function servicesAnswer(): string
    {
        $fromFaq = $this->faqs->answerForIntent('services');
        if ($fromFaq) {
            return $fromFaq;
        }

        return 'Ofrecemos mantenimiento preventivo y correctivo: cambio de aceite, frenos, revisión general, diagnóstico y más. '
            .'Si quieres un turno, escribe por ejemplo: agendar cita mañana cambio de aceite.';
    }

    private function datetimeClarify(string $message): string
    {
        $this->setContext([
            'last_topic' => 'datetime_hint',
            'datetime_text' => $message,
        ]);

        return '¿Quieres agendar una cita para ese día u horario? Escribe **agendar cita** o responde **sí** para continuar.';
    }

    private function isHoursQuery(string $normalized): bool
    {
        return Str::contains($normalized, [
            'horario', 'horarios',
            'a que hora abren', 'a que hora cierran', 'a que hora atienden',
            'cuando abren', 'cuando cierran', 'cuando atienden',
            'dias de atencion', 'dias que atienden', 'hora de atencion',
            'estan abiertos', 'esta abierto', 'hasta que hora',
        ]);
    }

    private function isServicesCatalogQuery(string $normalized): bool
    {
        if (Str::contains($normalized, [
            'mis servicios', 'historial de servicio', 'orden de servicio',
            'agendar', 'cita',
        ])) {
            return false;
        }

        return Str::contains($normalized, [
            'que servicios', 'cuales servicios', 'servicios ofrecen',
            'que ofrecen', 'mantenimiento preventivo', 'mantenimiento correctivo',
            'que trabajos', 'catalogo de servicios', 'que hacen',
            'servicios',
        ]);
    }

    private function isVehicleStatusQuery(string $normalized): bool
    {
        return Str::contains($normalized, [
            'estado del vehiculo', 'estado de mi', 'consultar el estado',
            'consultar estado', 'estado de mi auto', 'estado de mi carro',
            'mi auto', 'mi carro', 'mi vehiculo', 'mis autos', 'mi camioneta',
            'como va mi', 'como va el', 'en que va', 'donde esta',
            'que pasa con', 'situacion de',
            'ya esta listo', 'cuando lo entregan', 'cuando esta listo',
            'seguimiento de', 'listo mi auto',
            'informacion de mi', 'informacion del vehiculo', 'info de mi',
            'datos de mi vehiculo', 'datos de mi auto', 'datos del vehiculo',
            'que vehiculo tengo', 'que carro tengo', 'mis vehiculos',
        ]);
    }

    private function isExpenseQuery(string $normalized): bool
    {
        return Str::contains($normalized, [
            'gastos', 'cuanto he pagado', 'cuanto gaste', 'cuanto he gastado',
            'historial de pago', 'mis pagos', 'invertido', 'gastado',
            'cuanto dinero', 'dinero gastado', 'gasto total',
            'mis gastos', 'costo total', 'cuanto he invertido',
            'cuanto debo', 'resumen de gastos', 'historial de mantenimientos',
            'que he pagado', 'mis facturas',
        ]);
    }

    private function isOrderQuery(string $normalized): bool
    {
        return Str::contains($normalized, [
            'orden de servicio', 'ordenes activas', 'mis ordenes', 'trabajo en el taller',
            'orden actual', 'ordenes en proceso', 'que se esta haciendo',
            'avance del trabajo', 'progreso del trabajo', 'estado del trabajo',
            'mis servicios',
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

    private function isNegative(string $normalized): bool
    {
        return (bool) preg_match('/^(no|nop|nope|nel|mejor no|no gracias)\b/u', $normalized);
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
        return VehiclePlate::extract($text);
    }
}
