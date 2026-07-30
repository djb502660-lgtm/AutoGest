<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class VehicleModelTemplate extends Model
{
    protected $fillable = [
        'brand',
        'model',
        'maintenance_type',
        'title',
        'description',
        'interval_km',
        'interval_months',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function forVehicle(Vehicle $vehicle): Collection
    {
        return static::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(brand) = ?', [mb_strtolower($vehicle->brand)])
            ->whereRaw('LOWER(model) = ?', [mb_strtolower($vehicle->model)])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    public function syncSchedulesFor(Vehicle $vehicle): void
    {
        foreach (static::forVehicle($vehicle) as $template) {
            $template->createScheduleFor($vehicle);
        }
    }

    public function createScheduleFor(Vehicle $vehicle): MaintenanceSchedule
    {
        $scheduledDate = now()->addMonths($this->interval_months ?? 6)->toDateString();
        $mileageTarget = $this->interval_km
            ? $vehicle->mileage + $this->interval_km
            : null;

        return MaintenanceSchedule::firstOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'title' => $this->title,
                'status' => 'programado',
            ],
            [
                'maintenance_type' => $this->maintenance_type,
                'scheduled_date' => $scheduledDate,
                'mileage_target' => $mileageTarget,
                'notes' => $this->description,
            ],
        );
    }

    public function maintenanceTypeLabel(): string
    {
        return match ($this->maintenance_type) {
            'preventivo' => 'Preventivo',
            'correctivo' => 'Correctivo',
            default => ucfirst($this->maintenance_type),
        };
    }
}
