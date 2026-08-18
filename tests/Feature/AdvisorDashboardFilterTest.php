<?php

namespace Tests\Feature;

use App\Models\AppointmentRequest;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvisorDashboardFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stat_cards_link_to_filtered_lists(): void
    {
        $advisor = User::factory()->advisor()->create();

        $this->actingAs($advisor)
            ->get(route('advisor.dashboard'))
            ->assertOk()
            ->assertSee(route('advisor.orders.index', ['status' => 'en_proceso']), false)
            ->assertSee(route('advisor.orders.index', ['unassigned' => 1]), false)
            ->assertSee(route('advisor.chatbot-appointments.index'), false);
    }

    public function test_en_proceso_card_filters_orders_list(): void
    {
        $advisor = User::factory()->advisor()->create();
        $inProgress = $this->makeOrder($advisor, 'OS-PROC-1', 'en_proceso');
        $received = $this->makeOrder($advisor, 'OS-REC-1', 'recibida');

        $this->actingAs($advisor)
            ->get(route('advisor.orders.index', ['status' => 'en_proceso']))
            ->assertOk()
            ->assertSee('OS-PROC-1')
            ->assertDontSee('OS-REC-1');

        $this->assertNotNull($inProgress->id);
        $this->assertNotNull($received->id);
    }

    public function test_chatbot_card_opens_pending_requests_inbox(): void
    {
        $advisor = User::factory()->advisor()->create();
        $client = User::factory()->client()->create(['name' => 'Cliente Filtro']);
        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'FLT-001',
            'brand' => 'Kia',
            'model' => 'Rio',
            'year' => 2022,
            'mileage' => 10000,
            'status' => 'activo',
        ]);

        AppointmentRequest::create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'requested_date' => now()->toDateString(),
            'requested_time' => '10:00:00',
            'service_type' => 'Cambio de aceite',
            'description' => 'Chatbot',
            'status' => 'pendiente',
            'source' => 'chatbot',
        ]);

        $this->actingAs($advisor)
            ->get(route('advisor.dashboard'))
            ->assertOk()
            ->assertSee('Solicitudes chatbot')
            ->assertSee('FLT-001', false);

        $this->actingAs($advisor)
            ->get(route('advisor.chatbot-appointments.index'))
            ->assertOk()
            ->assertSee('FLT-001')
            ->assertSee('Cambio de aceite');
    }

    private function makeOrder(User $advisor, string $number, string $status): ServiceOrder
    {
        $client = User::factory()->client()->create();
        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ORD-'.fake()->unique()->numerify('###'),
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 20000,
            'status' => 'activo',
        ]);

        return ServiceOrder::create([
            'order_number' => $number,
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'advisor_id' => $advisor->id,
            'created_by' => $advisor->id,
            'source' => 'manual',
            'status' => $status,
            'progress' => $status === 'en_proceso' ? 40 : 0,
            'priority' => 'normal',
            'description' => 'Servicio '.$number,
        ]);
    }
}
