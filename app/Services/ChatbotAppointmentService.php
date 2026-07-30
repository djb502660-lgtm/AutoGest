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

    public function shouldHandle(string $text): bool
    {
        if (session()->has(self::SESSION_KEY)) {
            return true;
        }

        return $this->wantsAppointment($text);
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
            return $this->process($client, $text);
        } catch (Throwable $e) {
            Log::error('Chatbot appointment error: '.$e->getMessage(), ['exception' => $e]);
            session()->forget(self::SESSION_KEY);

            return 'No pude registrar la cita en este momento. Intenta de nuevo o contacta al taller.';
        }
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

            return "Perfecto.\n\nAhora indícame la hora deseada.\n\nEjemplos:\n\n• 09:00\n• 14:30\n• 3:00 pm";
        }

        $additionalWork = $this->extractAdditionalWork($text);
        $requiresApproval = $additionalWork !== null;
        $serviceType = $this->resolveServiceType($vehicle, $text);
        $description = $this->buildDescription($text, $serviceType, $additionalWork);

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

        if ($requiresApproval) {
            return "Registré tu solicitud #{$appointment->id} para el {$date->format('d/m/Y')}. "
                ."Un asesor revisará los trabajos adicionales y te confirmará la cita.";
        }

        return "Solicitud #{$appointment->id} registrada para el {$date->format('d/m/Y')}. "
            .'Un asesor de servicio te confirmará la cita pronto.';
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

        if (Str::contains($lower, ['en la mañana', 'por la mañana', 'mañana temprano'])) {
            return '09:00:00';
        }

        if (Str::contains($lower, ['en la tarde', 'por la tarde'])) {
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

    private function resolveServiceType(Vehicle $vehicle, string $text): string
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

        $template = VehicleModelTemplate::forVehicle($vehicle)->first();

        if ($template) {
            return $template->title;
        }

        return 'Mantenimiento general';
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
}
