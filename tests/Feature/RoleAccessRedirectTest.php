<?php

namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_visiting_admin_dashboard_is_sent_to_client_panel(): void
    {
        $client = User::factory()->client()->create();

        $this->actingAs($client)
            ->get(route('dashboard'))
            ->assertRedirect(route('client.dashboard'));
    }

    public function test_client_visiting_login_while_authenticated_is_sent_to_client_panel(): void
    {
        $client = User::factory()->client()->create();

        $this->actingAs($client)
            ->get(route('login'))
            ->assertRedirect(route('client.dashboard'));
    }

    public function test_client_login_ignores_admin_intended_url(): void
    {
        $client = User::factory()->client()->create([
            'email' => 'cliente-redirect@autogest.test',
            'password' => 'password',
        ]);

        $this->get('/dashboard')->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => $client->email,
            'password' => 'password',
        ])->assertRedirect(route('client.dashboard'));
    }

    public function test_advisor_visiting_client_chatbot_is_sent_to_advisor_panel(): void
    {
        $advisor = User::factory()->advisor()->create();

        $this->actingAs($advisor)
            ->get(route('client.chatbot.index'))
            ->assertRedirect(route('advisor.dashboard'));
    }

    public function test_client_can_open_chatbot_after_role_redirect(): void
    {
        $client = User::factory()->client()->create();

        $this->actingAs($client)
            ->get(route('client.chatbot.index'))
            ->assertOk()
            ->assertSee('AutoGest Bot');
    }

    public function test_admin_visiting_advisor_order_url_is_sent_to_admin_order(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->makeOrder($admin);

        $this->actingAs($admin)
            ->get('/asesor/ordenes/'.$order->id)
            ->assertRedirect(route('admin.orders.show', $order));
    }

    public function test_advisor_can_view_chatbot_order_created_by_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $advisor = User::factory()->advisor()->create();
        $order = $this->makeOrder($admin, [
            'source' => 'chatbot',
            'advisor_id' => $admin->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($advisor)
            ->get(route('advisor.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeOrder(User $actor, array $overrides = []): ServiceOrder
    {
        $client = User::factory()->client()->create();
        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'RD-'.fake()->unique()->numerify('####'),
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 10000,
            'status' => 'activo',
        ]);

        return ServiceOrder::create(array_merge([
            'order_number' => 'OS-RD-'.fake()->unique()->numerify('####'),
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'created_by' => $actor->id,
            'advisor_id' => $actor->isAdvisor() ? $actor->id : null,
            'source' => 'manual',
            'status' => 'recibida',
            'progress' => 0,
            'priority' => 'normal',
            'description' => 'Orden de prueba',
        ], $overrides));
    }
}
