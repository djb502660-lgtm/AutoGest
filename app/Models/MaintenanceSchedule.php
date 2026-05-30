<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceSchedule extends Model
{
    protected $fillable = [
        'vehicle_id',
        'title',
        'maintenance_type',
        'scheduled_date',
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

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function assignedMechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_mechanic_id');
    }
}
