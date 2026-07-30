<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\OrderComment;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintThreeOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_advisor_can_reassign_a_mechanic_from_order_detail(): void
    {
        $advisor = User::factory()->advisor()->create();
        $mechanicA = User::factory()->mechanic()->create();
        $mechanicB = User::factory()->mechanic()->create();
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-301',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2022,
            'mileage' => 54000,
            'status' => 'activo',
        ]);

        $order = ServiceOrder::create([
            'order_number' => 'OS-2026-0301',
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'mechanic_id' => $mechanicA->id,
            'advisor_id' => $advisor->id,
            'created_by' => $advisor->id,
            'source' => 'manual',
            'status' => 'recibida',
            'priority' => 'normal',
            'description' => 'Diagnóstico general',
            'progress' => 0,
        ]);

        $this->actingAs($advisor)
            ->put(route('advisor.orders.assign', $order), [
                'mechanic_id' => $mechanicB->id,
            ])
            ->assertRedirect(route('advisor.orders.show', $order));

        $this->assertDatabaseHas('service_orders', [
            'id' => $order->id,
            'mechanic_id' => $mechanicB->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'order.mechanic_assigned',
        ]);
    }

    public function test_completed_maintenance_updates_vehicle_order_and_comment_log(): void
    {
        $advisor = User::factory()->advisor()->create();
        $mechanic = User::factory()->mechanic()->create();
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-302',
            'brand' => 'Kia',
            'model' => 'Rio',
            'year' => 2020,
            'mileage' => 60000,
            'status' => 'en_taller',
        ]);

        $order = ServiceOrder::create([
            'order_number' => 'OS-2026-0302',
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'mechanic_id' => $mechanic->id,
            'advisor_id' => $advisor->id,
            'created_by' => $advisor->id,
            'source' => 'manual',
            'status' => 'en_proceso',
            'priority' => 'alta',
            'description' => 'Cambio de frenos',
            'progress' => 45,
            'started_at' => now()->subHour(),
        ]);

        $performedAt = now()->format('Y-m-d H:i:s');

        $this->actingAs($mechanic)
            ->post(route('mechanic.maintenances.store'), [
                'service_order_id' => $order->id,
                'vehicle_id' => $vehicle->id,
                'type' => 'correctivo',
                'description' => 'Cambio de pastillas y ajuste final',
                'mileage_at_service' => 60550,
                'parts_used' => 'Pastillas delanteras',
                'technical_notes' => 'Prueba de ruta completada sin ruidos.',
                'cost' => 140.50,
                'status' => 'completado',
                'performed_at' => $performedAt,
            ])
            ->assertRedirect(route('mechanic.history'));

        $order->refresh();
        $vehicle->refresh();

        $this->assertSame('completada', $order->status);
        $this->assertSame(100, $order->progress);
        $this->assertEquals('140.50', (string) $order->total_cost);
        $this->assertNotNull($order->completed_at);

        $this->assertSame(60550, $vehicle->mileage);
        $this->assertSame('activo', $vehicle->status);

        $this->assertDatabaseHas('order_comments', [
            'service_order_id' => $order->id,
            'user_id' => $mechanic->id,
        ]);

        $this->assertTrue(
            OrderComment::query()
                ->where('service_order_id', $order->id)
                ->where('user_id', $mechanic->id)
                ->where('comment', 'like', '%Cambio de pastillas y ajuste final%')
                ->exists()
        );

        $this->assertTrue(
            ActivityLog::query()->where('action', 'maintenance.created')->exists()
        );
    }
}
