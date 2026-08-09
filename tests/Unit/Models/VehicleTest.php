<?php

namespace Tests\Unit\Models;

use App\Models\Alert;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    private function vehicle(array $attributes = []): Vehicle
    {
        return (new Vehicle)->forceFill($attributes);
    }

    public function test_display_name_combines_brand_model_and_plate(): void
    {
        $vehicle = $this->vehicle(['brand' => 'Toyota', 'model' => 'Corolla', 'plate' => 'ABC-123']);

        $this->assertSame('Toyota Corolla (ABC-123)', $vehicle->displayName());
    }

    public function test_status_label_translates_known_statuses(): void
    {
        $this->assertSame('Activo', $this->vehicle(['status' => 'activo'])->statusLabel());
        $this->assertSame('Inactivo', $this->vehicle(['status' => 'inactivo'])->statusLabel());
        $this->assertSame('En taller', $this->vehicle(['status' => 'en_taller'])->statusLabel());
    }

    public function test_status_label_falls_back_to_a_capitalized_raw_status(): void
    {
        $this->assertSame('Vendido', $this->vehicle(['status' => 'vendido'])->statusLabel());
    }

    public function test_status_badge_class_maps_statuses_to_colors(): void
    {
        $this->assertSame('green', $this->vehicle(['status' => 'activo'])->statusBadgeClass());
        $this->assertSame('yellow', $this->vehicle(['status' => 'en_taller'])->statusBadgeClass());
        $this->assertSame('red', $this->vehicle(['status' => 'inactivo'])->statusBadgeClass());
    }

    public function test_dates_are_cast_to_date_instances(): void
    {
        $casts = $this->vehicle()->getCasts();

        $this->assertSame('date', $casts['insurance_expiry']);
        $this->assertSame('date', $casts['inspection_expiry']);
        $this->assertSame('date', $casts['registration_date']);
    }

    public function test_client_id_is_the_foreign_key_of_the_client_relation(): void
    {
        $relation = $this->vehicle()->client();

        $this->assertInstanceOf(User::class, $relation->getRelated());
        $this->assertSame('client_id', $relation->getForeignKeyName());
    }

    public function test_vehicle_owns_its_operational_records(): void
    {
        $vehicle = $this->vehicle();

        $this->assertInstanceOf(ServiceOrder::class, $vehicle->serviceOrders()->getRelated());
        $this->assertInstanceOf(Maintenance::class, $vehicle->maintenances()->getRelated());
        $this->assertInstanceOf(MaintenanceSchedule::class, $vehicle->maintenanceSchedules()->getRelated());
        $this->assertInstanceOf(Alert::class, $vehicle->alerts()->getRelated());
        $this->assertSame('vehicles.id', $vehicle->serviceOrders()->getQualifiedParentKeyName());
        $this->assertSame('service_orders.vehicle_id', $vehicle->serviceOrders()->getQualifiedForeignKeyName());
    }
}
