<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Alert;
use App\Models\AppointmentRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleModelTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ChatbotAppointmentService
{
    private const SESSION_KEY = 'chatbot_appointment_draft';

    private const SESSION_MANAGE_KEY = 'chatbot_appointment_manage';

    /** @var list<string> */
    private const MANAGEABLE_STATUSES = ['pendiente', 'confirmada'];

    public function shouldHandle(string $text): bool
    {
        if (session()->has(self::SESSION_MANAGE_KEY)) {
            return true;
        }

        if (session()->has(self::SESSION_KEY)) {
            return true;
        }

        if ($this->wantsManage($text)) {
            return true;
        }

        if ($this->wantsAppointment($text)) {
            return true;
        }

        return $this->parseDate($text) !== null || $this->parseTime($text) !== null;
    }

    public function wantsManage(string $text): bool
    {
        if (in_array(trim($text), ['4', '4️⃣'], true)) {
            return true;
        }

        $lower = Str::lower($text);
        $phrases = [
            'mis citas', 'ver citas', 'ver mis citas', 'listar citas', 'consultar citas',
            'cancelar cita', 'cancelar mi cita', 'cancelar solicitud', 'anular cita',
            'quiero cancelar', 'quiero eliminar', 'eliminar cita', 'eliminar mi cita',
            'editar cita', 'editar mi cita', 'modificar cita', 'modificar mi cita',
            'quiero editar', 'quiero modificar', 'cambiar cita', 'reprogramar cita',
            'reprogramar mi cita', 'reprogramar', 'tengo alguna cita', 'tengo cita',
            'citas esta semana', 'historial de citas', 'mover cita', 'mover mi cita',
            'pasala', 'pásala', 'moverla', 'cambiarla',
        ];

        foreach ($phrases as $phrase) {
            if (Str::contains($lower, $phrase)) {
                return true;
            }
        }

        return false;
    }

    public function wantsAppointment(string $text): bool
    {
        $lower = Str::lower($text);

        $phrases = [
            'agendar', 'agendo', 'agenda una', 'agenda cita', 'agenda mi',
            'reservar', 'reserva', 'solicitar cita', 'solicitud de cita',
            'quiero cita', 'quiero una cita', 'pedir cita', 'programar cita',
            'necesito cita', 'necesito una cita', 'generar cita', 'hacer cita',
            'cita para', 'cita el', 'cita mañana', 'cita manana',
            'turno para', 'turno el', 'sacar cita', 'agendar turno',
            'quiero que le hagan', 'quiero que me hagan', 'necesito que le hagan',
            'necesito que me hagan', 'quiero una revision', 'quiero revision',
            'necesito una revision', 'necesito revision',
        ];

        foreach ($phrases as $phrase) {
            if (Str::contains($lower, $phrase)) {
                return true;
            }
        }

        return false;
    }

    public function handle(User $client, string $text): string
    {
        try {
            // Limpiar sesión completamente si el usuario inicia una nueva intención de gestión
            if ($this->wantsCancel($text) || $this->wantsEdit($text) || $this->wantsManage($text)) {
                session()->forget(self::SESSION_KEY);
                session()->forget(self::SESSION_MANAGE_KEY);
            }

            // Si hay sesión de gestión pero el usuario quiere cancelar/editar, limpiar
            if (session()->has(self::SESSION_MANAGE_KEY) && ($this->wantsCancel($text) || $this->wantsEdit($text))) {
                session()->forget(self::SESSION_MANAGE_KEY);
            }

            if ($this->shouldManage($client, $text)) {
                return $this->processManage($client, $text);
            }

            return $this->process($client, $text);
        } catch (Throwable $e) {
            Log::error('Chatbot appointment error: '.$e->getMessage(), ['exception' => $e]);
            session()->forget(self::SESSION_KEY);
            session()->forget(self::SESSION_MANAGE_KEY);

            return 'No pude procesar tu solicitud de cita en este momento. Intenta de nuevo o contacta al taller.';
        }
    }

    private function shouldManage(User $client, string $text): bool
    {
        if (session()->has(self::SESSION_MANAGE_KEY)) {
            return true;
        }

        if ($this->wantsManage($text)) {
            session()->forget(self::SESSION_KEY);
            session()->forget(self::SESSION_MANAGE_KEY);

            return true;
        }

        if (! $this->wantsAppointment($text) && ($this->parseDate($text) !== null || $this->parseTime($text) !== null)) {
            $hasManageable = AppointmentRequest::query()
                ->where('client_id', $client->id)
                ->whereIn('status', self::MANAGEABLE_STATUSES)
                ->exists();

            if ($hasManageable) {
                session()->forget(self::SESSION_KEY);
                session()->forget(self::SESSION_MANAGE_KEY);

                return true;
            }
        }

        return false;
    }

    private function process(User $client, string $text): string
    {
        /** @var array{vehicle_id?: int, requested_date?: string, service_hint?: string} $draft */
        $draft = session(self::SESSION_KEY, []);

        $vehicle = $this->resolveVehicle($client, $text)
            ?? (isset($draft['vehicle_id']) ? $client->vehicles()->find($draft['vehicle_id']) : null);

        if (! $vehicle) {
            if ($client->vehicles()->count() === 0) {
                session()->forget(self::SESSION_KEY);

                return 'No tienes vehículos registrados. Contacta al taller para registrarlos antes de agendar.';
            }

            session([self::SESSION_KEY => ['step' => 'vehicle', 'service_hint' => $text]]);

            return 'Indícame la placa del vehículo para agendar la cita (ej: ABC-123).';
        }

        $draft['vehicle_id'] = $vehicle->id;

        $parsedDate = $this->parseDate($text);
        $parsedTime = $this->parseTime($text);
        $date = $parsedDate
            ?? (isset($draft['requested_date']) ? Carbon::parse($draft['requested_date']) : null);
        $time = $parsedTime
            ?? ($draft['requested_time'] ?? null);

        if (! $date) {
            session([self::SESSION_KEY => array_merge($draft, ['step' => 'date'])]);

            return "Vehículo encontrado:\n\n🚗 {$vehicle->brand} {$vehicle->model}\n\nAhora indícame la fecha deseada para la cita.\n\nPuedes escribir, por ejemplo:\n\n• mañana\n• viernes\n• 15/08/2026";
        }

        if ($date->endOfDay()->isPast()) {
            session([self::SESSION_KEY => array_merge($draft, ['step' => 'date'])]);

            return "Esa fecha ya pasó. Indícame una fecha futura (ej: mañana, 15/08 o viernes).";
        }

        $draft['requested_date'] = $date->toDateString();
        $draft['requested_time'] = $time;

        if ($this->isDayFull($date)) {
            $nextAvailable = $this->findNextAvailableDate($date);
            session([self::SESSION_KEY => array_merge($draft, ['step' => 'date'])]);

            return "El {$date->format('d/m/Y')} ya está lleno. El siguiente día disponible es {$nextAvailable->format('d/m/Y')}. ¿Quieres agendar para esa fecha o indica otra fecha?";
        }

        if (! $time) {
            session([self::SESSION_KEY => array_merge($draft, ['step' => 'time'])]);

            return "Perfecto.\n\n".$this->promptAvailableSlots($date);
        }

        $currentStep = $draft['step'] ?? null;
        $serviceReason = $draft['service_reason'] ?? null;

        if ($currentStep === 'service') {
            if ($this->isValidServiceReason($text)) {
                $serviceReason = trim($text);
            } else {
                session([self::SESSION_KEY => array_merge($draft, ['step' => 'service'])]);

                return $this->serviceReasonPrompt($date, $time);
            }
        } elseif (! $serviceReason && $this->hasExplicitServiceReason($text)) {
            $serviceReason = trim($text);
        } elseif (! $serviceReason && isset($draft['service_hint']) && $this->hasExplicitServiceReason($draft['service_hint'])) {
            $serviceReason = trim($draft['service_hint']);
        }

        if (! $serviceReason) {
            session([self::SESSION_KEY => array_merge($draft, [
                'step' => 'service',
                'requested_date' => $date->toDateString(),
                'requested_time' => $time,
                'vehicle_id' => $vehicle->id,
            ])]);

            return $this->serviceReasonPrompt($date, $time);
        }

        $additionalWork = $this->extractAdditionalWork($serviceReason);
        $requiresApproval = $additionalWork !== null;
        $serviceType = $this->resolveServiceTypeFromReason($vehicle, $serviceReason);
        $description = $this->buildDescription($serviceReason, $serviceType, $additionalWork);

        $appointment = AppointmentRequest::create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'requested_date' => $date->toDateString(),
            'requested_time' => $time,
            'service_type' => $serviceType,
            'description' => $description,
            'additional_work' => $additionalWork,
            'requires_approval' => $requiresApproval,
            'status' => 'pendiente',
            'source' => 'chatbot',
        ]);

        $this->notifyAdvisors($appointment);
        session()->forget(self::SESSION_KEY);

        $timeFormatted = $this->formatTime($time);
        $vehicleLabel = "{$vehicle->brand} {$vehicle->model}";

        if ($requiresApproval) {
            return "Solicitud #{$appointment->id} registrada para el {$date->format('d/m/Y')}.\n\n"
                ."Perfecto.\n\n"
                ."🚗 Vehículo: {$vehicleLabel}\n"
                ."📌 Placa: {$vehicle->plate}\n"
                ."📅 {$date->format('d/m/Y')}\n"
                ."🕐 {$timeFormatted}\n"
                ."📝 Motivo: {$serviceType}\n\n"
                ."Estado: 🟡 Pendiente de confirmación.\n\n"
                ."Un asesor revisará los trabajos adicionales y te confirmará la cita.";
        }

        return "Solicitud #{$appointment->id} registrada para el {$date->format('d/m/Y')}.\n\n"
            ."Perfecto.\n\n"
            ."🚗 Vehículo: {$vehicleLabel}\n"
            ."📌 Placa: {$vehicle->plate}\n"
            ."📅 {$date->format('d/m/Y')}\n"
            ."🕐 {$timeFormatted}\n"
            ."📝 Motivo: {$serviceType}\n\n"
            ."Estado: 🟡 Pendiente de confirmación.\n\n"
            ."Un asesor revisará la disponibilidad y recibirás una notificación cuando la cita sea confirmada.";
    }

    private function serviceReasonPrompt(Carbon $date, string $time): string
    {
        $timeFormatted = Carbon::createFromFormat('H:i:s', $time)->format('g:i A');

        return "Perfecto, cita el {$date->format('d/m/Y')} a las {$timeFormatted}.\n\n"
            ."¿Cuál es el motivo o tipo de servicio que necesitas?\n\n"
            ."Ejemplos:\n• Cambio de aceite\n• Revisión de frenos\n• Revisión general\n• Diagnóstico de ruido en el motor";
    }

    private function isValidServiceReason(string $text): bool
    {
        $trimmed = trim($text);

        if (mb_strlen($trimmed) < 3) {
            return false;
        }

        if (preg_match('/^\s*(\d{1,2})\s*$/', $trimmed)) {
            return false;
        }

        if (preg_match('/\b([A-Z]{2,3}[-\s]?\d{2,4})\b/i', $trimmed) && mb_strlen($trimmed) <= 12) {
            return false;
        }

        if ($this->parseDate($trimmed) && ! $this->hasExplicitServiceReason($trimmed)) {
            return false;
        }

        return true;
    }

    private function hasExplicitServiceReason(string $text): bool
    {
        if ($this->mapKnownServiceType($text) !== null) {
            return true;
        }

        $lower = Str::lower(trim($text));
        $keywords = [
            'alineacion', 'alineación', 'balanceo', 'bateria', 'batería', 'llanta', 'llantas',
            'diagnostico', 'diagnóstico', 'filtro', 'mantenimiento', 'ruido', 'fuga', 'reparar',
            'revisar', 'chequeo', 'servicio', 'clutch', 'embrague', 'suspension', 'suspensión',
            'transmision', 'transmisión', 'pastilla', 'amortiguador', 'correa', 'injection',
            'inyeccion', 'inyección', 'electrico', 'eléctrico', 'aire acondicionado',
        ];

        foreach ($keywords as $keyword) {
            if (Str::contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function mapKnownServiceType(string $text): ?string
    {
        $lower = Str::lower($text);

        if (Str::contains($lower, ['freno', 'frenos'])) {
            return 'Revisión de frenos';
        }
        if (Str::contains($lower, ['aceite'])) {
            return 'Cambio de aceite';
        }
        if (Str::contains($lower, ['revision', 'revisión'])) {
            return 'Revisión general';
        }

        return null;
    }

    private function resolveServiceTypeFromReason(Vehicle $vehicle, string $reason): string
    {
        if ($mapped = $this->mapKnownServiceType($reason)) {
            return $mapped;
        }

        $template = VehicleModelTemplate::forVehicle($vehicle)->first();

        if ($template && Str::contains(Str::lower($reason), Str::lower($template->title))) {
            return $template->title;
        }

        return Str::limit(ucfirst(trim($reason)), 100);
    }

    public function cancelDraft(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    private function resolveVehicle(User $client, string $text): ?Vehicle
    {
        if (preg_match('/\b([A-Z]{2,3}[-\s]?\d{2,4})\b/i', $text, $matches)) {
            $plate = $this->normalizePlate($matches[1]);

            return $client->vehicles()
                ->get()
                ->first(fn (Vehicle $vehicle) => $this->normalizePlate($vehicle->plate) === $plate);
        }

        if ($client->vehicles()->count() === 1) {
            return $client->vehicles()->first();
        }

        return null;
    }

    private function parseDate(string $text): ?Carbon
    {
        $lower = Str::lower($text);

        if (preg_match('/\bhoy\b/u', $lower)) {
            return now()->startOfDay();
        }

        if (Str::contains($lower, 'pasado manana') || Str::contains($lower, 'pasado mañana')) {
            return now()->addDays(2)->startOfDay();
        }

        if (Str::contains($lower, 'manana') || Str::contains($lower, 'mañana')) {
            return now()->addDay()->startOfDay();
        }

        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{2,4}))?/', $text, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = isset($m[3]) && $m[3] !== '' ? (int) $m[3] : (int) now()->year;
            if ($year < 100) {
                $year += 2000;
            }

            try {
                return Carbon::createFromDate($year, $month, $day)->startOfDay();
            } catch (\Exception) {
                return null;
            }
        }

        if (preg_match('/\b(\d{1,2})\s+de\s+(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)(?:\s+(\d{2,4}))?\b/ui', $lower, $m)) {
            $day = (int) $m[1];
            $monthName = $m[2];
            $year = isset($m[3]) && $m[3] !== '' ? (int) $m[3] : (int) now()->year;
            $months = [
                'enero' => 1,
                'febrero' => 2,
                'marzo' => 3,
                'abril' => 4,
                'mayo' => 5,
                'junio' => 6,
                'julio' => 7,
                'agosto' => 8,
                'septiembre' => 9,
                'octubre' => 10,
                'noviembre' => 11,
                'diciembre' => 12,
            ];

            $month = $months[Str::lower($monthName)] ?? null;
            if ($month !== null) {
                try {
                    return Carbon::createFromDate($year, $month, $day)->startOfDay();
                } catch (\Exception) {
                    return null;
                }
            }
        }

        $days = [
            'lunes' => Carbon::MONDAY,
            'martes' => Carbon::TUESDAY,
            'miércoles' => Carbon::WEDNESDAY,
            'miercoles' => Carbon::WEDNESDAY,
            'jueves' => Carbon::THURSDAY,
            'viernes' => Carbon::FRIDAY,
            'sábado' => Carbon::SATURDAY,
            'sabado' => Carbon::SATURDAY,
            'domingo' => Carbon::SUNDAY,
        ];

        foreach ($days as $name => $constant) {
            if (preg_match('/\b'.preg_quote($name, '/').'\b/u', $lower)) {
                $date = now()->startOfDay();

                if (preg_match('/\bproximo\s+'.preg_quote($name, '/').'\b/u', $lower)
                    || preg_match('/\bpróximo\s+'.preg_quote($name, '/').'\b/u', $lower)) {
                    return $date->next($constant);
                }

                if (preg_match('/\beste\s+'.preg_quote($name, '/').'\b/u', $lower)) {
                    if ((int) $date->dayOfWeek === $constant) {
                        return $date;
                    }

                    return (int) $date->dayOfWeek < $constant
                        ? $date->copy()->next($constant)
                        : $date->copy()->next($constant);
                }

                if ((int) $date->dayOfWeek === $constant) {
                    return $date;
                }

                return $date->next($constant);
            }
        }

        return null;
    }

    private function parseTime(string $text): ?string
    {
        if (preg_match('/(\d{1,2}):(\d{2})/', $text, $m)) {
            return sprintf('%02d:%02d:00', (int) $m[1], (int) $m[2]);
        }

        if (preg_match('/(?:a\s+las|alas|a la)\s+(\d{1,2})\b/i', $text, $m)) {
            $hour = (int) $m[1];

            if ($hour >= 0 && $hour <= 23) {
                return sprintf('%02d:00:00', $hour);
            }
        }

        $lower = Str::lower($text);
        $hourWords = [
            'nueve' => 9, 'diez' => 10, 'once' => 11, 'doce' => 12,
            'una' => 13, 'dos' => 14, 'tres' => 15, 'cuatro' => 16,
            'cinco' => 17, 'seis' => 18,
        ];

        foreach ($hourWords as $word => $hour) {
            if (preg_match('/(?:a\s+las|alas|a la)\s+'.$word.'\b/u', $lower)) {
                return sprintf('%02d:00:00', $hour);
            }
        }

        if (preg_match('/\b(nueve|diez|once|doce|una|dos|tres|cuatro|cinco|seis)\b/u', $lower, $m)) {
            $hour = $hourWords[$m[1]] ?? null;
            if ($hour !== null) {
                return sprintf('%02d:00:00', $hour);
            }
        }

        if (preg_match('/(\d{1,2})\s*(am|pm|a\.m\.|p\.m\.)/i', $text, $m)) {
            $hour = (int) $m[1];
            if (Str::contains(Str::lower($m[2]), 'p') && $hour < 12) {
                $hour += 12;
            }

            return sprintf('%02d:00:00', $hour);
        }

        if (preg_match('/^\s*(\d{1,2})\s*$/', $text, $m)) {
            $hour = (int) $m[1];
            if ($hour >= 0 && $hour <= 23) {
                return sprintf('%02d:00:00', $hour);
            }
        }

        $lower = Str::lower($text);

        if (Str::contains($lower, ['en la mañana', 'por la mañana', 'para la mañana', 'mañana temprano'])) {
            return '09:00:00';
        }

        if (Str::contains($lower, ['en la tarde', 'por la tarde', 'para la tarde'])) {
            return '15:00:00';
        }

        if (Str::contains($lower, ['en la noche', 'por la noche'])) {
            return '19:00:00';
        }

        return null;
    }

    private function extractAdditionalWork(string $text): ?string
    {
        $lower = Str::lower($text);
        $markers = ['también', 'tambien', 'además', 'ademas', 'extra', 'adicional', 'ruido', 'fuga', 'vibra'];

        foreach ($markers as $marker) {
            $pos = mb_strpos($lower, $marker);
            if ($pos !== false) {
                return trim(mb_substr($text, $pos));
            }
        }

        return null;
    }

    private function isDayFull(Carbon $date): bool
    {
        return AppointmentRequest::query()
            ->where('requested_date', $date->toDateString())
            ->whereIn('status', ['pendiente', 'confirmada', 'convertida'])
            ->count() >= 5;
    }

    private function findNextAvailableDate(Carbon $date): Carbon
    {
        $current = $date->copy()->addDay();

        while ($this->isDayFull($current)) {
            $current->addDay();
        }

        return $current;
    }

    private function buildDescription(string $text, string $serviceType, ?string $additionalWork): string
    {
        $base = "Solicitud vía chatbot: {$serviceType}. Mensaje: ".Str::limit(trim($text), 400);
        if ($additionalWork) {
            $base .= ' Trabajo adicional: '.$additionalWork;
        }

        return Str::limit($base, 900);
    }

    private function notifyAdvisors(AppointmentRequest $appointment): void
    {
        $appointment->load('client', 'vehicle');

        $message = "Cliente {$appointment->client->name} — {$appointment->vehicle->plate} — "
            ."{$appointment->service_type} el ".$appointment->requested_date->format('d/m/Y').'.';

        if ($appointment->requires_approval) {
            $message .= ' Requiere revisión de trabajos adicionales.';
        }

        User::query()
            ->where('role', UserRole::Advisor)
            ->where('status', 'activo')
            ->each(function (User $advisor) use ($appointment, $message) {
                Alert::create([
                    'vehicle_id' => $appointment->vehicle_id,
                    'user_id' => $advisor->id,
                    'type' => 'custom',
                    'title' => 'Nueva solicitud de cita (chatbot)',
                    'message' => $message,
                    'severity' => $appointment->requires_approval ? 'warning' : 'info',
                    'due_date' => $appointment->requested_date,
                ]);
            });
    }

    private function normalizePlate(string $plate): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/i', '', $plate) ?? '');
    }

    // ─────────────────────────────────────────────────────────────
    // GESTIÓN DE CITAS: CONSULTAR, EDITAR, CANCELAR
    // ─────────────────────────────────────────────────────────────

    private function processManage(User $client, string $text): string
    {
        /** @var array<string, mixed> $manage */
        $manage = session(self::SESSION_MANAGE_KEY, []);
        $lower = Str::lower(trim($text));

        // Limpiar sesión de agendar si el usuario quiere gestionar citas
        if ($this->wantsManage($text) && session()->has(self::SESSION_KEY)) {
            session()->forget(self::SESSION_KEY);
        }

        // Si el usuario quiere cancelar o editar, limpiar cualquier estado previo
        if ($this->wantsCancel($text) || $this->wantsEdit($text)) {
            if (!empty($manage) && !in_array($manage['step'] ?? '', ['cancel_confirm', 'edit_field', 'edit_date', 'edit_time', 'edit_reason'])) {
                session()->forget(self::SESSION_MANAGE_KEY);
                $manage = [];
            }
        }

        if ($this->isAffirmative($lower) && ($manage['step'] ?? '') === 'cancel_confirm') {
            return $this->confirmCancel($client, $manage);
        }

        if ($this->isNegative($lower) && ($manage['step'] ?? '') === 'cancel_confirm') {
            session()->forget(self::SESSION_MANAGE_KEY);

            return 'Entendido, tu cita se mantiene sin cambios. ¿Hay algo más en lo que pueda ayudarte?';
        }

        // Priorizar detección de cancelación sobre edición
        if (empty($manage)) {
            if ($this->wantsCancel($text)) {
                return $this->startCancelFlow($client);
            }

            if ($this->wantsEdit($text) || $this->wantsReschedule($text)) {
                return $this->startEditFlow($client, $text);
            }

            if ($this->parseDate($text) || $this->parseTime($text)) {
                return $this->startEditFlow($client, $text);
            }

            return $this->listAppointments($client, $text);
        }

        return match ($manage['step'] ?? '') {
            'idle' => $this->handleIdleManage($client, $text, $manage),
            'select' => $this->handleSelectAppointment($client, $text, $manage),
            'edit_field' => $this->handleEditFieldChoice($client, $text, $manage),
            'edit_date' => $this->handleEditDate($client, $text, $manage),
            'edit_time' => $this->handleEditTime($client, $text, $manage),
            'edit_reason' => $this->handleEditReason($client, $text, $manage),
            'cancel_confirm' => $this->handleCancelConfirm($client, $text, $manage),
            default => $this->listAppointments($client, $text),
        };
    }

    private function handleIdleManage(User $client, string $text, array $manage): string
    {
        if ($this->wantsCancel($text)) {
            return $this->startCancelFlow($client);
        }

        if ($this->wantsEdit($text) || $this->wantsReschedule($text)) {
            return $this->startEditFlow($client, $text);
        }

        $appointment = $this->findManagedAppointment($client, $manage);

        if ($appointment && ($time = $this->parseTime($text))) {
            return $this->applyTimeUpdate($client, $appointment, $time);
        }

        if ($appointment && ($date = $this->parseDate($text))) {
            return $this->applyDateUpdate($client, $appointment, $date);
        }

        if ($appointment) {
            return $this->formatAppointmentDetail($appointment, true)
                ."\n\n¿Deseas cambiar la **fecha**, la **hora** o **cancelar** la cita?";
        }

        session()->forget(self::SESSION_MANAGE_KEY);

        return 'No pude encontrar la cita en contexto. ¿Deseas consultar tus citas activas?';
    }

    private function wantsCancel(string $text): bool
    {
        $lower = Str::lower($text);

        return Str::contains($lower, [
            'cancelar cita', 'cancelar mi cita', 'cancelar solicitud',
            'anular cita', 'eliminar cita', 'eliminar mi cita',
            'quiero cancelar', 'quiero eliminar', 'quiero anular',
        ]);
    }

    private function wantsEdit(string $text): bool
    {
        $lower = Str::lower($text);

        return Str::contains($lower, [
            'editar cita', 'editar mi cita', 'modificar cita', 'modificar mi cita',
            'cambiar cita', 'cambiar mi cita', 'cambiar la cita', 'cambiar la fecha',
            'cambiar la hora', 'mover cita', 'mover mi cita',
            'quiero editar', 'quiero modificar', 'quiero cambiar',
        ]);
    }

    private function wantsReschedule(string $text): bool
    {
        $lower = Str::lower($text);

        return Str::contains($lower, [
            'reprogramar', 'pasala', 'pásala', 'moverla', 'cambiarla',
            'para la tarde', 'para la mañana', 'otro horario', 'otra fecha',
        ]);
    }

    private function wantsListAppointments(string $text): bool
    {
        $lower = Str::lower($text);

        return Str::contains($lower, [
            'mis citas', 'ver citas', 'ver mis citas', 'listar citas', 'consultar citas',
            'tengo alguna cita', 'tengo cita', 'citas esta semana', 'historial de citas',
            'todas mis citas', 'muéstrame mis citas', 'muestrame mis citas',
        ]);
    }

    private function listAppointments(User $client, string $text): string
    {
        $lower = Str::lower($text);
        $onlyActive = ! Str::contains($lower, ['historial', 'todas', 'anteriores', 'pasadas']);
        $thisWeek = Str::contains($lower, ['esta semana', 'semana']);

        $query = AppointmentRequest::query()
            ->where('client_id', $client->id)
            ->with('vehicle')
            ->orderByDesc('requested_date')
            ->orderByDesc('requested_time');

        if ($onlyActive) {
            $query->whereIn('status', self::MANAGEABLE_STATUSES);
        }

        if ($thisWeek) {
            $query->whereBetween('requested_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ]);
        }

        $appointments = $query->get();

        if ($appointments->isEmpty()) {
            session()->forget(self::SESSION_MANAGE_KEY);

            return $onlyActive
                ? 'No tienes citas activas en este momento. Si deseas, puedo ayudarte a agendar una nueva cita.'
                : 'Aún no tienes citas registradas en AutoGest.';
        }

        if ($appointments->count() === 1) {
            $appointment = $appointments->first();
            session([
                self::SESSION_MANAGE_KEY => [
                    'step' => 'idle',
                    'appointment_id' => $appointment->id,
                ],
            ]);

            return $this->formatAppointmentDetail($appointment, $onlyActive);
        }

        $lines = $appointments->map(function (AppointmentRequest $a) {
            $emoji = match ($a->status) {
                'confirmada' => '✅',
                'cancelada' => '❌',
                'rechazada' => '❌',
                'convertida' => '✅',
                default => '🟡',
            };

            return "{$emoji} {$a->requested_date->format('d/m/Y')} {$this->formatTime($a->requested_time)} — {$a->service_type} ({$a->statusLabel()})";
        })->join("\n");

        session([
            self::SESSION_MANAGE_KEY => [
                'step' => 'select',
                'action' => 'view',
            ],
        ]);

        return "Este es tu historial de citas:\n\n{$lines}\n\n"
            ."Si deseas modificar o cancelar alguna, indícame la fecha o el número de la cita.";
    }

    private function startCancelFlow(User $client): string
    {
        session()->forget(self::SESSION_KEY);
        session()->forget(self::SESSION_MANAGE_KEY);

        $appointment = $this->resolveManageableAppointment($client);

        if (! $appointment) {
            session()->forget(self::SESSION_MANAGE_KEY);

            return 'No encontré citas activas que puedas cancelar.';
        }

        session([
            self::SESSION_MANAGE_KEY => [
                'step' => 'cancel_confirm',
                'appointment_id' => $appointment->id,
            ],
        ]);

        return "Encontré la siguiente cita:\n\n"
            .$this->formatAppointmentSummary($appointment)
            ."\n\n¿Estás seguro de cancelarla?\n\nResponde **Sí** o **No**.";
    }

    private function startEditFlow(User $client, string $text): string
    {
        $appointment = $this->resolveManageableAppointment($client);

        if (! $appointment) {
            session()->forget(self::SESSION_MANAGE_KEY);

            return 'No encontré citas activas que puedas modificar.';
        }

        if ($parsedDate = $this->parseDate($text)) {
            session([
                self::SESSION_MANAGE_KEY => [
                    'step' => 'edit_date',
                    'appointment_id' => $appointment->id,
                    'parsed_date' => $parsedDate->toDateString(),
                ],
            ]);

            return $this->handleEditDate($client, $text, session(self::SESSION_MANAGE_KEY));
        }

        if ($parsedTime = $this->parseTime($text)) {
            session([
                self::SESSION_MANAGE_KEY => [
                    'step' => 'edit_time',
                    'appointment_id' => $appointment->id,
                ],
            ]);

            return $this->applyTimeUpdate($client, $appointment, $parsedTime);
        }

        session([
            self::SESSION_MANAGE_KEY => [
                'step' => 'edit_field',
                'appointment_id' => $appointment->id,
            ],
        ]);

        return "Tu cita actual es:\n\n"
            .$this->formatAppointmentSummary($appointment)
            ."\n\n¿Qué deseas modificar?\n\n• Fecha\n• Hora\n• Motivo del servicio\n\n"
            .'También puedes escribir directamente la nueva fecha u hora.';
    }

    private function handleSelectAppointment(User $client, string $text, array $manage): string
    {
        $appointment = $this->findAppointmentByReference($client, $text);

        if (! $appointment) {
            return 'No pude identificar la cita. Indícame la fecha (ej: 06/08) o el motivo del servicio.';
        }

        session([
            self::SESSION_MANAGE_KEY => [
                'step' => 'edit_field',
                'appointment_id' => $appointment->id,
            ],
        ]);

        return $this->formatAppointmentDetail($appointment, true);
    }

    private function handleEditFieldChoice(User $client, string $text, array $manage): string
    {
        $appointment = $this->findManagedAppointment($client, $manage);
        if (! $appointment) {
            session()->forget(self::SESSION_MANAGE_KEY);

            return 'No pude encontrar la cita. Intenta consultar tus citas nuevamente.';
        }

        $lower = Str::lower($text);

        if ($parsedDate = $this->parseDate($text)) {
            return $this->applyDateUpdate($client, $appointment, $parsedDate);
        }

        if ($parsedTime = $this->parseTime($text)) {
            return $this->applyTimeUpdate($client, $appointment, $parsedTime);
        }

        if (Str::contains($lower, ['fecha', 'dia', 'día'])) {
            session([self::SESSION_MANAGE_KEY => array_merge($manage, ['step' => 'edit_date'])]);

            return '¿Para qué fecha deseas reprogramar la cita? (ej: viernes, 15/08/2026)';
        }

        if (Str::contains($lower, ['hora', 'horario'])) {
            session([self::SESSION_MANAGE_KEY => array_merge($manage, ['step' => 'edit_time'])]);

            return $this->promptAvailableSlots($appointment->requested_date);
        }

        if (Str::contains($lower, ['motivo', 'servicio', 'razon', 'razón'])) {
            session([self::SESSION_MANAGE_KEY => array_merge($manage, ['step' => 'edit_reason'])]);

            return 'Indícame el nuevo motivo o tipo de servicio para tu cita.';
        }

        return '¿Qué deseas modificar: **fecha**, **hora** o **motivo del servicio**?';
    }

    private function handleEditDate(User $client, string $text, array $manage): string
    {
        $appointment = $this->findManagedAppointment($client, $manage);
        if (! $appointment) {
            session()->forget(self::SESSION_MANAGE_KEY);

            return 'No pude encontrar la cita seleccionada.';
        }

        $date = isset($manage['parsed_date'])
            ? Carbon::parse($manage['parsed_date'])
            : $this->parseDate($text);

        if (! $date) {
            return 'No entendí la fecha. Escríbela como: viernes, mañana o 15/08/2026.';
        }

        if ($date->endOfDay()->isPast()) {
            return 'Esa fecha ya pasó. Indícame una fecha futura.';
        }

        return $this->applyDateUpdate($client, $appointment, $date);
    }

    private function handleEditTime(User $client, string $text, array $manage): string
    {
        $appointment = $this->findManagedAppointment($client, $manage);
        if (! $appointment) {
            session()->forget(self::SESSION_MANAGE_KEY);

            return 'No pude encontrar la cita seleccionada.';
        }

        $time = $this->parseTime($text);

        if (! $time) {
            return $this->promptAvailableSlots($appointment->requested_date);
        }

        return $this->applyTimeUpdate($client, $appointment, $time);
    }

    private function handleEditReason(User $client, string $text, array $manage): string
    {
        $appointment = $this->findManagedAppointment($client, $manage);
        if (! $appointment) {
            session()->forget(self::SESSION_MANAGE_KEY);

            return 'No pude encontrar la cita seleccionada.';
        }

        if (! $this->isValidServiceReason($text)) {
            return 'Indícame un motivo válido para el servicio (ej: revisión de frenos, cambio de aceite).';
        }

        $appointment->load('vehicle');
        $serviceType = $this->resolveServiceTypeFromReason($appointment->vehicle, $text);

        $appointment->update([
            'service_type' => $serviceType,
            'description' => $this->buildDescription($text, $serviceType, null),
            'status' => 'pendiente',
        ]);

        $this->notifyAdvisorsOfChange($appointment, 'actualizada', 'Motivo actualizado');
        session()->forget(self::SESSION_MANAGE_KEY);

        return "Listo. 😊\n\nTu cita fue actualizada correctamente.\n\n"
            .$this->formatAppointmentSummary($appointment->fresh())
            ."\n\nEstado: 🟡 Pendiente de confirmación.";
    }

    private function handleCancelConfirm(User $client, string $text, array $manage): string
    {
        if ($this->isAffirmative(Str::lower($text))) {
            return $this->confirmCancel($client, $manage);
        }

        if ($this->isNegative(Str::lower($text))) {
            session()->forget(self::SESSION_MANAGE_KEY);

            return 'Entendido, tu cita se mantiene sin cambios.';
        }

        return 'Por favor responde **Sí** para confirmar la cancelación o **No** para mantener la cita.';
    }

    private function confirmCancel(User $client, array $manage): string
    {
        $appointment = $this->findManagedAppointment($client, $manage);

        if (! $appointment || ! in_array($appointment->status, self::MANAGEABLE_STATUSES, true)) {
            session()->forget(self::SESSION_MANAGE_KEY);

            return 'No pude cancelar la cita. Es posible que ya haya sido procesada.';
        }

        $this->notifyAdvisorsOfChange($appointment->fresh(['client', 'vehicle']), 'cancelada');
        $appointment->update(['status' => 'cancelada']);
        session()->forget(self::SESSION_MANAGE_KEY);

        return "Tu cita ha sido cancelada correctamente.\n\n"
            .'Si más adelante deseas agendar una nueva cita, estaré encantado de ayudarte.';
    }

    private function applyDateUpdate(User $client, AppointmentRequest $appointment, Carbon $date): string
    {
        if ($this->isDayFull($date) && $appointment->requested_date->toDateString() !== $date->toDateString()) {
            $next = $this->findNextAvailableDate($date);

            return "El {$date->format('d/m/Y')} ya está lleno. El siguiente día disponible es {$next->format('d/m/Y')}. ¿Deseas esa fecha?";
        }

        $previousDate = $appointment->requested_date->format('d/m/Y');
        $appointment->update([
            'requested_date' => $date->toDateString(),
            'status' => 'pendiente',
        ]);

        $this->notifyAdvisorsOfChange($appointment->fresh(['client', 'vehicle']), 'actualizada', "Fecha: {$previousDate} → {$date->format('d/m/Y')}");

        session([
            self::SESSION_MANAGE_KEY => [
                'step' => 'edit_time',
                'appointment_id' => $appointment->id,
            ],
        ]);

        return "Perfecto.\n\nPara el {$date->format('d/m/Y')} tengo disponibilidad:\n\n"
            .$this->formatSlotList($date)
            ."\n\n¿Qué horario prefieres?";
    }

    private function applyTimeUpdate(User $client, AppointmentRequest $appointment, string $time): string
    {
        $previousTime = $this->formatTime($appointment->requested_time);
        $appointment->update([
            'requested_time' => $time,
            'status' => 'pendiente',
        ]);

        $this->notifyAdvisorsOfChange($appointment->fresh(['client', 'vehicle']), 'actualizada', "Hora: {$previousTime} → {$this->formatTime($time)}");
        session()->forget(self::SESSION_MANAGE_KEY);

        return "Listo. 😊\n\nTu cita fue actualizada correctamente.\n\n"
            .$this->formatAppointmentSummary($appointment->fresh())
            ."\n\nEstado: 🟡 Pendiente de confirmación.";
    }

    private function resolveManageableAppointment(User $client): ?AppointmentRequest
    {
        $manage = session(self::SESSION_MANAGE_KEY, []);
        if (! empty($manage['appointment_id'])) {
            $found = $this->findManagedAppointment($client, $manage);
            if ($found) {
                return $found;
            }
        }

        return AppointmentRequest::query()
            ->where('client_id', $client->id)
            ->whereIn('status', self::MANAGEABLE_STATUSES)
            ->orderBy('requested_date')
            ->orderBy('requested_time')
            ->first();
    }

    private function findManagedAppointment(User $client, array $manage): ?AppointmentRequest
    {
        if (empty($manage['appointment_id'])) {
            return null;
        }

        return AppointmentRequest::query()
            ->where('client_id', $client->id)
            ->whereKey($manage['appointment_id'])
            ->whereIn('status', array_merge(self::MANAGEABLE_STATUSES, ['cancelada']))
            ->first();
    }

    private function findAppointmentByReference(User $client, string $text): ?AppointmentRequest
    {
        if (preg_match('/#(\d+)/', $text, $m)) {
            return AppointmentRequest::query()
                ->where('client_id', $client->id)
                ->whereKey((int) $m[1])
                ->first();
        }

        if ($date = $this->parseDate($text)) {
            return AppointmentRequest::query()
                ->where('client_id', $client->id)
                ->whereDate('requested_date', $date->toDateString())
                ->whereIn('status', self::MANAGEABLE_STATUSES)
                ->first();
        }

        $lower = Str::lower($text);

        return AppointmentRequest::query()
            ->where('client_id', $client->id)
            ->whereIn('status', self::MANAGEABLE_STATUSES)
            ->where(function ($q) use ($lower) {
                $q->whereRaw('LOWER(service_type) LIKE ?', ['%'.$lower.'%'])
                    ->orWhereRaw('LOWER(description) LIKE ?', ['%'.$lower.'%']);
            })
            ->orderBy('requested_date')
            ->first();
    }

    private function formatAppointmentSummary(AppointmentRequest $appointment): string
    {
        $appointment->loadMissing('vehicle');

        return '📅 '.$appointment->requested_date->format('d/m/Y')."\n"
            .'🕐 '.$this->formatTime($appointment->requested_time)."\n"
            .'🚗 '.($appointment->vehicle?->plate ?? '—')."\n"
            .'📝 '.$appointment->service_type;
    }

    private function formatAppointmentDetail(AppointmentRequest $appointment, bool $showActions): string
    {
        $statusEmoji = match ($appointment->status) {
            'confirmada' => '🟢',
            'cancelada', 'rechazada' => '🔴',
            default => '🟡',
        };

        $statusLabel = match ($appointment->status) {
            'pendiente' => 'Pendiente de confirmación',
            default => $appointment->statusLabel(),
        };

        $body = "Sí, tienes una cita registrada.\n\n"
            .$this->formatAppointmentSummary($appointment)
            ."\n\nEstado: {$statusEmoji} {$statusLabel}.";

        if ($showActions && in_array($appointment->status, self::MANAGEABLE_STATUSES, true)) {
            $body .= "\n\nSi deseas puedes:\n• Cambiar la fecha\n• Cambiar la hora\n• Cancelar la cita";
        }

        return $body;
    }

    private function formatTime(?string $time): string
    {
        if (! $time) {
            return '—';
        }

        try {
            return Carbon::createFromFormat('H:i:s', $time)->format('g:i A');
        } catch (\Exception) {
            return $time;
        }
    }

    private function promptAvailableSlots(Carbon|string $date): string
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        return "Tengo disponibilidad:\n\n".$this->formatSlotList($date)."\n\n¿Cuál horario prefieres?";
    }

    private function formatSlotList(Carbon $date): string
    {
        $slots = $this->getAvailableSlots($date);

        if ($slots === []) {
            return 'No hay horarios disponibles ese día. Indica otra fecha.';
        }

        return collect($slots)
            ->map(fn (string $slot) => '🕐 '.$slot)
            ->join("\n");
    }

    /** @return list<string> */
    private function getAvailableSlots(Carbon $date): array
    {
        $booked = AppointmentRequest::query()
            ->where('requested_date', $date->toDateString())
            ->whereIn('status', ['pendiente', 'confirmada', 'convertida'])
            ->pluck('requested_time')
            ->filter()
            ->map(fn (?string $t) => substr((string) $t, 0, 5))
            ->all();

        $all = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];

        return array_values(array_filter($all, fn (string $slot) => ! in_array($slot, $booked, true)));
    }

    private function notifyAdvisorsOfChange(AppointmentRequest $appointment, string $action, ?string $detail = null): void
    {
        $appointment->loadMissing('client', 'vehicle');

        $titles = [
            'actualizada' => 'Cita actualizada por cliente (chatbot)',
            'cancelada' => 'Cita cancelada por cliente (chatbot)',
        ];

        $message = "Cliente {$appointment->client->name} — {$appointment->vehicle->plate} — "
            ."{$appointment->service_type} el ".$appointment->requested_date->format('d/m/Y')
            .' a las '.$this->formatTime($appointment->requested_time).'.';

        if ($detail) {
            $message .= " Cambio: {$detail}.";
        }

        User::query()
            ->where('role', UserRole::Advisor)
            ->where('status', 'activo')
            ->each(function (User $advisor) use ($appointment, $message, $titles, $action) {
                Alert::create([
                    'vehicle_id' => $appointment->vehicle_id,
                    'user_id' => $advisor->id,
                    'type' => 'custom',
                    'title' => $titles[$action] ?? 'Actualización de cita (chatbot)',
                    'message' => $message,
                    'severity' => $action === 'cancelada' ? 'warning' : 'info',
                    'due_date' => $appointment->requested_date,
                ]);
            });
    }

    private function isAffirmative(string $text): bool
    {
        return (bool) preg_match('/\b(si|sí|confirmo|confirmar|de acuerdo|ok|vale|claro|por supuesto)\b/u', $text);
    }

    private function isNegative(string $text): bool
    {
        return (bool) preg_match('/\b(no|nop|negativo|mejor no|cancelar eso)\b/u', $text);
    }
}
