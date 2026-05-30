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
        'year',
        'color',
        'mileage',
        'vin',
        'photo',
        'status',
        'insurance_expiry',
        'inspection_expiry',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'insurance_expiry' => 'date',
            'inspection_expiry' => 'date',
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
}
