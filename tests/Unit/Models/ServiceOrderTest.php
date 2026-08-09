<?php

namespace Tests\Unit\Models;

use App\Models\Maintenance;
use App\Models\OrderComment;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attributes = []): ServiceOrder
    {
        return (new ServiceOrder)->forceFill($attributes);
    }

    public function test_status_label_translates_known_statuses(): void
    {
        $this->assertSame('Recibida', $this->order(['status' => 'recibida'])->statusLabel());
        $this->assertSame('En proceso', $this->order(['status' => 'en_proceso'])->statusLabel());
        $this->assertSame('Completado', $this->order(['status' => 'completada'])->statusLabel());
        $this->assertSame('Entregado', $this->order(['status' => 'entregada'])->statusLabel());
        $this->assertSame('Cancelado', $this->order(['status' => 'cancelada'])->statusLabel());
    }

    public function test_status_label_falls_back_to_a_capitalized_raw_status(): void
    {
        $this->assertSame('Pausada', $this->order(['status' => 'pausada'])->statusLabel());
    }

    public function test_status_badge_class_maps_statuses_to_colors(): void
    {
        $this->assertSame('green', $this->order(['status' => 'completada'])->statusBadgeClass());
        $this->assertSame('green', $this->order(['status' => 'entregada'])->statusBadgeClass());
        $this->assertSame('yellow', $this->order(['status' => 'en_proceso'])->statusBadgeClass());
        $this->assertSame('yellow', $this->order(['status' => 'recibida'])->statusBadgeClass());
        $this->assertSame('red', $this->order(['status' => 'cancelada'])->statusBadgeClass());
        $this->assertSame('yellow', $this->order(['status' => 'pausada'])->statusBadgeClass());
    }

    public function test_costs_and_dates_are_cast(): void
    {
        $casts = $this->order()->getCasts();

        $this->assertSame('datetime', $casts['scheduled_at']);
        $this->assertSame('datetime', $casts['started_at']);
        $this->assertSame('datetime', $casts['completed_at']);
        $this->assertSame('decimal:2', $casts['estimated_cost']);
        $this->assertSame('decimal:2', $casts['total_cost']);
    }

    public function test_user_relations_use_their_own_foreign_keys(): void
    {
        $order = $this->order();

        $this->assertSame('client_id', $order->client()->getForeignKeyName());
        $this->assertSame('mechanic_id', $order->mechanic()->getForeignKeyName());
        $this->assertSame('advisor_id', $order->advisor()->getForeignKeyName());
        $this->assertSame('created_by', $order->creator()->getForeignKeyName());
        $this->assertInstanceOf(User::class, $order->creator()->getRelated());
        $this->assertInstanceOf(Vehicle::class, $order->vehicle()->getRelated());
        $this->assertInstanceOf(Maintenance::class, $order->maintenances()->getRelated());
        $this->assertInstanceOf(OrderComment::class, $order->comments()->getRelated());
    }

    public function test_generate_order_number_starts_at_one_for_the_current_year(): void
    {
        $this->assertSame(sprintf('OS-%d-0001', now()->year), ServiceOrder::generateOrderNumber());
    }

    public function test_generate_order_number_increments_with_the_orders_of_the_current_year(): void
    {
        $client = User::factory()->client()->create();
        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'NUM-001',
            'brand' => 'Toyota',
            'model' => 'Hilux',
            'year' => 2021,
            'mileage' => 1000,
            'status' => 'activo',
        ]);

        foreach (['OS-0001', 'OS-0002'] as $number) {
            ServiceOrder::create([
                'order_number' => $number,
                'vehicle_id' => $vehicle->id,
                'client_id' => $client->id,
                'created_by' => $client->id,
                'status' => 'recibida',
                'description' => 'Revisión',
            ]);
        }

        $this->assertSame(sprintf('OS-%d-0003', now()->year), ServiceOrder::generateOrderNumber());
    }

    public function test_generate_order_number_ignores_orders_from_other_years(): void
    {
        $client = User::factory()->client()->create();
        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'NUM-002',
            'brand' => 'Nissan',
            'model' => 'Sentra',
            'year' => 2020,
            'mileage' => 2000,
            'status' => 'activo',
        ]);

        $old = ServiceOrder::create([
            'order_number' => 'OS-OLD',
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'created_by' => $client->id,
            'status' => 'entregada',
            'description' => 'Orden anterior',
        ]);
        $old->forceFill(['created_at' => now()->subYear()])->save();

        $this->assertSame(sprintf('OS-%d-0001', now()->year), ServiceOrder::generateOrderNumber());
    }
}
