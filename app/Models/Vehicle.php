<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'client_id',
        'plate',
        'brand',
        'model',
        'sub_model',
        'year',
        'color',
        'mileage',
        'vin',
        'engine_number',
        'transmission_type',
        'tire_size',
        'photo',
        'status',
        'insurance_expiry',
        'inspection_expiry',
        'registration_date',
        'paint_reference',
        'transponder',
        'radio_code',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'insurance_expiry' => 'date',
            'inspection_expiry' => 'date',
            'registration_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function displayName(): string
    {
        return "{$this->brand} {$this->model} ({$this->plate})";
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'activo' => 'Activo',
            'inactivo' => 'Inactivo',
            'en_taller' => 'En taller',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'activo' => 'green',
            'en_taller' => 'yellow',
            default => 'red',
        };
    }
}
