<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceSchedule extends Model
{
    protected $fillable = [
        'client_id',
        'vehicle_id',
        'title',
        'service_type',
        'scheduled_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'mileage_target',
        'assigned_mechanic_id',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function assignedMechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_mechanic_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'programado' => 'Programado',
            'confirmado' => 'Confirmado',
            'en_taller' => 'En Taller',
            'completado' => 'Completado',
            'vencido' => 'Vencido',
            'cancelado' => 'Cancelado',
            default => ucfirst($this->status),
        };
    }

    public function colorClass(): string
    {
        return match ($this->status) {
            'completado' => 'event-green',
            'en_taller' => 'event-yellow',
            'vencido' => 'event-red',
            'cancelado' => 'event-muted',
            'confirmado' => 'event-teal',
            default => 'event-blue',
        };
    }
}
