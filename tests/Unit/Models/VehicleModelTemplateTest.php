<?php

namespace Tests\Unit\Models;

use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleModelTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleModelTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function vehicle(array $attributes = []): Vehicle
    {
        return Vehicle::create($attributes + [
            'client_id' => User::factory()->client()->create()->id,
            'plate' => 'TPL-'.fake()->unique()->numberBetween(100, 999),
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2022,
            'mileage' => 40000,
            'status' => 'activo',
        ]);
    }

    private function template(array $attributes = []): VehicleModelTemplate
    {
        return VehicleModelTemplate::create($attributes + [
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'maintenance_type' => 'preventivo',
            'title' => 'Cambio de aceite',
            'description' => 'Aceite y filtro',
            'interval_km' => 5000,
            'interval_months' => 6,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_for_vehicle_matches_brand_and_model_case_insensitively(): void
    {
        $this->template(['brand' => 'TOYOTA', 'model' => 'COROLLA', 'title' => 'Frenos']);

        $templates = VehicleModelTemplate::forVehicle($this->vehicle(['brand' => 'toyota', 'model' => 'corolla']));

        $this->assertSame(['Frenos'], $templates->pluck('title')->all());
    }

    public function test_for_vehicle_skips_inactive_templates_and_other_models(): void
    {
        $this->template(['title' => 'Activa']);
        $this->template(['title' => 'Inactiva', 'is_active' => false]);
        $this->template(['title' => 'Otro modelo', 'model' => 'Hilux']);

        $this->assertSame(['Activa'], VehicleModelTemplate::forVehicle($this->vehicle())->pluck('title')->all());
    }

    public function test_for_vehicle_orders_by_sort_order_then_title(): void
    {
        $this->template(['title' => 'Bujías', 'sort_order' => 2]);
        $this->template(['title' => 'Zapatas', 'sort_order' => 1]);
        $this->template(['title' => 'Aceite', 'sort_order' => 1]);

        $this->assertSame(
            ['Aceite', 'Zapatas', 'Bujías'],
            VehicleModelTemplate::forVehicle($this->vehicle())->pluck('title')->all(),
        );
    }

    public function test_create_schedule_for_uses_the_interval_to_project_date_and_mileage(): void
    {
        $vehicle = $this->vehicle(['mileage' => 40000]);
        $schedule = $this->template(['interval_km' => 5000, 'interval_months' => 3])->createScheduleFor($vehicle);

        $this->assertSame($vehicle->id, $schedule->vehicle_id);
        $this->assertSame('Cambio de aceite', $schedule->title);
        $this->assertSame('programado', $schedule->status);
        $this->assertSame(45000, $schedule->mileage_target);
        $this->assertSame(now()->addMonths(3)->toDateString(), $schedule->scheduled_date->toDateString());
        $this->assertSame('Aceite y filtro', $schedule->notes);
    }

    public function test_create_schedule_for_defaults_to_six_months_and_no_mileage_target(): void
    {
        $schedule = $this->template(['interval_km' => null, 'interval_months' => null])
            ->createScheduleFor($this->vehicle());

        $this->assertNull($schedule->mileage_target);
        $this->assertSame(now()->addMonths(6)->toDateString(), $schedule->scheduled_date->toDateString());
    }

    public function test_create_schedule_for_is_idempotent(): void
    {
        $vehicle = $this->vehicle();
        $template = $this->template();

        $first = $template->createScheduleFor($vehicle);
        $second = $template->createScheduleFor($vehicle);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, MaintenanceSchedule::count());
    }

    public function test_sync_schedules_for_creates_one_schedule_per_active_template(): void
    {
        $this->template(['title' => 'Aceite']);
        $this->template(['title' => 'Frenos']);
        $this->template(['title' => 'Inactiva', 'is_active' => false]);

        $this->template()->syncSchedulesFor($this->vehicle());

        $this->assertEqualsCanonicalizing(
            ['Aceite', 'Frenos', 'Cambio de aceite'],
            MaintenanceSchedule::pluck('title')->all(),
        );
    }

    public function test_maintenance_type_label_translates_the_type(): void
    {
        $this->assertSame('Preventivo', (new VehicleModelTemplate)->forceFill(['maintenance_type' => 'preventivo'])->maintenanceTypeLabel());
        $this->assertSame('Correctivo', (new VehicleModelTemplate)->forceFill(['maintenance_type' => 'correctivo'])->maintenanceTypeLabel());
        $this->assertSame('Garantia', (new VehicleModelTemplate)->forceFill(['maintenance_type' => 'garantia'])->maintenanceTypeLabel());
    }

    public function test_is_active_is_cast_to_a_boolean(): void
    {
        $this->assertSame('boolean', (new VehicleModelTemplate)->getCasts()['is_active']);
    }
}
