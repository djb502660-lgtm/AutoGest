<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentRequest extends Model
{
    protected $fillable = [
        'client_id',
        'vehicle_id',
        'advisor_id',
        'service_order_id',
        'requested_date',
        'requested_time',
        'service_type',
        'description',
        'additional_work',
        'requires_approval',
        'status',
        'source',
        'advisor_notes',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'requires_approval' => 'boolean',
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

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pendiente' => 'Pendiente',
            'confirmada' => 'Confirmada',
            'rechazada' => 'Rechazada',
            'convertida' => 'Convertida a orden',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'convertida', 'confirmada' => 'green',
            'rechazada' => 'red',
            default => 'yellow',
        };
    }
}
