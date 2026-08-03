<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    protected $fillable = [
        'service_order_id',
        'vehicle_id',
        'mechanic_id',
        'type',
        'description',
        'mileage_at_service',
        'fuel_level',
        'inventory_spare_wheel',
        'inventory_tools',
        'inventory_radio',
        'inventory_documents',
        'parts_used',
        'technical_notes',
        'cost',
        'parts_cost',
        'labor_cost',
        'status',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'parts_cost' => 'decimal:2',
            'labor_cost' => 'decimal:2',
            'performed_at' => 'datetime',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'completado' => 'green',
            'en_proceso' => 'yellow',
            'pendiente' => 'yellow',
            default => 'red',
        };
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'preventivo' => 'Preventivo',
            'correctivo' => 'Correctivo',
            'garantia' => 'Garantía',
            default => ucfirst($this->type),
        };
    }
}
