<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Alert;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateAutomaticAlerts extends Command
{
    protected $signature = 'autogest:generate-alerts';
    protected $description = 'Genera alertas automáticas por vencimiento de seguro, revisión técnica y mantenimientos programados';

    private int $failures = 0;

    public function handle(): int
    {
        $this->info('Generando alertas automáticas...');

        $this->failures = 0;
        $alertsCreated = 0;

        // Alertas por vencimiento de seguro (30 días antes)
        $alertsCreated += $this->generateInsuranceExpiryAlerts();

        // Alertas por vencimiento de revisión técnica (30 días antes)
        $alertsCreated += $this->generateInspectionExpiryAlerts();

        // Alertas por mantenimientos programados próximos (7 días antes)
        $alertsCreated += $this->generateMaintenanceScheduleAlerts();

        $this->info("Se generaron {$alertsCreated} alertas automáticas.");

        if ($this->failures > 0) {
            $this->error("{$this->failures} alertas no pudieron generarse. Revisa el log para más detalles.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Ejecuta la generación de alertas de un registro sin abortar el resto del lote.
     */
    private function attempt(string $context, array $meta, callable $callback): bool
    {
        try {
            $callback();

            return true;
        } catch (Throwable $e) {
            $this->failures++;

            report($e);

            Log::error("[autogest:generate-alerts] {$context}", $meta + [
                'exception' => $e->getMessage(),
            ]);

            $this->warn("No se pudo generar la alerta ({$context}): {$e->getMessage()}");

            return false;
        }
    }

    private function generateInsuranceExpiryAlerts(): int
    {
        $count = 0;
        $warningDays = 30;
        $criticalDays = 7;
        
        $vehicles = Vehicle::where('status', 'activo')
            ->whereNotNull('insurance_expiry')
            ->where('insurance_expiry', '>', now())
            ->where('insurance_expiry', '<=', now()->addDays($warningDays))
            ->get();

        foreach ($vehicles as $vehicle) {
            $daysUntil = now()->diffInDays($vehicle->insurance_expiry);
            $severity = $daysUntil <= $criticalDays ? 'critical' : 'warning';
            $title = $severity === 'critical' 
                ? '¡Seguro vence pronto!' 
                : 'Seguro próximo a vencer';
            
            $message = "El seguro del vehículo {$vehicle->plate} ({$vehicle->brand} {$vehicle->model}) vence el {$vehicle->insurance_expiry->format('d/m/Y')}.";

            $created = $this->attempt(
                'alerta de vencimiento de seguro',
                ['vehicle_id' => $vehicle->id],
                function () use ($vehicle, $title, $message, $severity) {
                    // Alerta para el cliente
                    $this->createAlertIfNotExists(
                        $vehicle->client_id,
                        $vehicle->id,
                        'insurance_expiry',
                        $title,
                        $message,
                        $severity,
                        $vehicle->insurance_expiry
                    );

                    // Alerta para administradores
                    $this->createAlertForAdmins(
                        $vehicle->id,
                        'insurance_expiry',
                        $title,
                        $message,
                        $severity,
                        $vehicle->insurance_expiry
                    );
                }
            );

            if ($created) {
                $count++;
            }
        }

        if ($count > 0) {
            $this->info("- {$count} alertas de vencimiento de seguro generadas.");
        }
        
        return $count;
    }

    private function generateInspectionExpiryAlerts(): int
    {
        $count = 0;
        $warningDays = 30;
        $criticalDays = 7;
        
        $vehicles = Vehicle::where('status', 'activo')
            ->whereNotNull('inspection_expiry')
            ->where('inspection_expiry', '>', now())
            ->where('inspection_expiry', '<=', now()->addDays($warningDays))
            ->get();

        foreach ($vehicles as $vehicle) {
            $daysUntil = now()->diffInDays($vehicle->inspection_expiry);
            $severity = $daysUntil <= $criticalDays ? 'critical' : 'warning';
            $title = $severity === 'critical' 
                ? '¡Revisión técnica vence pronto!' 
                : 'Revisión técnica próxima a vencer';
            
            $message = "La revisión técnica del vehículo {$vehicle->plate} ({$vehicle->brand} {$vehicle->model}) vence el {$vehicle->inspection_expiry->format('d/m/Y')}.";

            $created = $this->attempt(
                'alerta de vencimiento de revisión técnica',
                ['vehicle_id' => $vehicle->id],
                function () use ($vehicle, $title, $message, $severity) {
                    // Alerta para el cliente
                    $this->createAlertIfNotExists(
                        $vehicle->client_id,
                        $vehicle->id,
                        'inspection_expiry',
                        $title,
                        $message,
                        $severity,
                        $vehicle->inspection_expiry
                    );

                    // Alerta para administradores
                    $this->createAlertForAdmins(
                        $vehicle->id,
                        'inspection_expiry',
                        $title,
                        $message,
                        $severity,
                        $vehicle->inspection_expiry
                    );
                }
            );

            if ($created) {
                $count++;
            }
        }

        if ($count > 0) {
            $this->info("- {$count} alertas de vencimiento de revisión técnica generadas.");
        }
        
        return $count;
    }

    private function generateMaintenanceScheduleAlerts(): int
    {
        $count = 0;
        $warningDays = 7;
        
        $schedules = MaintenanceSchedule::where('status', 'programado')
            ->where('scheduled_date', '>', now())
            ->where('scheduled_date', '<=', now()->addDays($warningDays))
            ->with('vehicle')
            ->get();

        foreach ($schedules as $schedule) {
            $daysUntil = now()->diffInDays($schedule->scheduled_date);
            $severity = $daysUntil <= 2 ? 'warning' : 'info';
            $title = "Mantenimiento programado: {$schedule->title}";
            
            $message = "Mantenimiento '{$schedule->title}' para vehículo {$schedule->vehicle->plate} programado para el {$schedule->scheduled_date->format('d/m/Y')}.";

            $created = $this->attempt(
                'alerta de mantenimiento programado',
                ['schedule_id' => $schedule->id, 'vehicle_id' => $schedule->vehicle_id],
                function () use ($schedule, $title, $message, $severity) {
                    // Alerta para el cliente
                    $this->createAlertIfNotExists(
                        $schedule->vehicle->client_id,
                        $schedule->vehicle_id,
                        'maintenance_due',
                        $title,
                        $message,
                        $severity,
                        $schedule->scheduled_date
                    );

                    // Alerta para administradores
                    $this->createAlertForAdmins(
                        $schedule->vehicle_id,
                        'maintenance_due',
                        $title,
                        $message,
                        $severity,
                        $schedule->scheduled_date
                    );
                }
            );

            if ($created) {
                $count++;
            }
        }

        if ($count > 0) {
            $this->info("- {$count} alertas de mantenimientos programados generadas.");
        }
        
        return $count;
    }

    private function createAlertIfNotExists(
        int $userId,
        int $vehicleId,
        string $type,
        string $title,
        string $message,
        string $severity,
        Carbon $dueDate
    ): void {
        $existing = Alert::where('user_id', $userId)
            ->where('vehicle_id', $vehicleId)
            ->where('type', $type)
            ->where('due_date', $dueDate->toDateString())
            ->where('is_resolved', false)
            ->first();

        if ($existing) {
            return;
        }

        Alert::create([
            'user_id' => $userId,
            'vehicle_id' => $vehicleId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'severity' => $severity,
            'due_date' => $dueDate,
            'is_resolved' => false,
        ]);
    }

    private function createAlertForAdmins(
        int $vehicleId,
        string $type,
        string $title,
        string $message,
        string $severity,
        Carbon $dueDate
    ): void {
        $admins = User::where('role', UserRole::Admin)
            ->where('status', 'activo')
            ->get();

        foreach ($admins as $admin) {
            $existing = Alert::where('user_id', $admin->id)
                ->where('vehicle_id', $vehicleId)
                ->where('type', $type)
                ->where('due_date', $dueDate->toDateString())
                ->where('is_resolved', false)
                ->first();

            if ($existing) {
                continue;
            }

            Alert::create([
                'user_id' => $admin->id,
                'vehicle_id' => $vehicleId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'severity' => $severity,
                'due_date' => $dueDate,
                'is_resolved' => false,
            ]);
        }
    }
}
