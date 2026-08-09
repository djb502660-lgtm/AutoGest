<?php

namespace Tests\Unit\Models;

use App\Models\Maintenance;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    private function maintenance(array $attributes = []): Maintenance
    {
        return (new Maintenance)->forceFill($attributes);
    }

    public function test_status_label_translates_known_statuses(): void
    {
        $this->assertSame('Pendiente', $this->maintenance(['status' => 'pendiente'])->statusLabel());
        $this->assertSame('En proceso', $this->maintenance(['status' => 'en_proceso'])->statusLabel());
        $this->assertSame('Completado', $this->maintenance(['status' => 'completado'])->statusLabel());
        $this->assertSame('Cancelado', $this->maintenance(['status' => 'cancelado'])->statusLabel());
        $this->assertSame('Pausado', $this->maintenance(['status' => 'pausado'])->statusLabel());
    }

    public function test_status_badge_class_maps_statuses_to_colors(): void
    {
        $this->assertSame('green', $this->maintenance(['status' => 'completado'])->statusBadgeClass());
        $this->assertSame('yellow', $this->maintenance(['status' => 'en_proceso'])->statusBadgeClass());
        $this->assertSame('yellow', $this->maintenance(['status' => 'pendiente'])->statusBadgeClass());
        $this->assertSame('red', $this->maintenance(['status' => 'cancelado'])->statusBadgeClass());
    }

    public function test_type_label_translates_known_types(): void
    {
        $this->assertSame('Preventivo', $this->maintenance(['type' => 'preventivo'])->typeLabel());
        $this->assertSame('Correctivo', $this->maintenance(['type' => 'correctivo'])->typeLabel());
        $this->assertSame('Garantía', $this->maintenance(['type' => 'garantia'])->typeLabel());
        $this->assertSame('Revision', $this->maintenance(['type' => 'revision'])->typeLabel());
    }

    public function test_costs_and_performed_at_are_cast(): void
    {
        $casts = $this->maintenance()->getCasts();

        $this->assertSame('decimal:2', $casts['cost']);
        $this->assertSame('decimal:2', $casts['parts_cost']);
        $this->assertSame('decimal:2', $casts['labor_cost']);
        $this->assertSame('datetime', $casts['performed_at']);
    }

    public function test_relations_point_to_the_expected_models(): void
    {
        $maintenance = $this->maintenance();

        $this->assertInstanceOf(ServiceOrder::class, $maintenance->serviceOrder()->getRelated());
        $this->assertInstanceOf(Vehicle::class, $maintenance->vehicle()->getRelated());
        $this->assertInstanceOf(User::class, $maintenance->mechanic()->getRelated());
        $this->assertSame('mechanic_id', $maintenance->mechanic()->getForeignKeyName());
    }
}
