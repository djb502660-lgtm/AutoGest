<?php

namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\ServicePhoto;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhotoLightboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_order_photos_open_in_shared_lightbox_instead_of_a_new_tab(): void
    {
        [$client, $order] = $this->orderWithReceptionPhoto();

        $this->actingAs($client)
            ->get(route('client.orders.show', $order))
            ->assertOk()
            ->assertSee('id="photoLightbox"', false)
            ->assertSee('data-lightbox="order-'.$order->id.'"', false)
            ->assertSee('data-lightbox-caption="Recepción"', false)
            ->assertSee('js-photo-lightbox', false)
            ->assertDontSee("window.open(", false);
    }

    public function test_admin_order_photos_use_the_same_lightbox(): void
    {
        [$client, $order] = $this->orderWithReceptionPhoto();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('id="photoLightbox"', false)
            ->assertSee('data-lightbox="order-'.$order->id.'"', false)
            ->assertDontSee("window.open(", false);
    }

    public function test_mechanic_order_photos_use_the_same_lightbox(): void
    {
        $mechanic = User::factory()->mechanic()->create();
        [$client, $order] = $this->orderWithReceptionPhoto($mechanic);

        $this->actingAs($mechanic)
            ->get(route('mechanic.orders.show', $order))
            ->assertOk()
            ->assertSee('id="photoLightbox"', false)
            ->assertSee('data-lightbox="order-'.$order->id.'"', false)
            ->assertSee('AutoGestLightbox', false)
            ->assertDontSee("window.open(", false);
    }

    public function test_panel_layout_always_includes_the_lightbox_modal(): void
    {
        $client = User::factory()->client()->create();

        $this->actingAs($client)
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('id="photoLightbox"', false);
    }

    /**
     * @return array{0: User, 1: ServiceOrder}
     */
    private function orderWithReceptionPhoto(?User $mechanic = null): array
    {
        $client = User::factory()->client()->create();
        $mechanic ??= User::factory()->mechanic()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'LBX-'.fake()->unique()->numerify('###'),
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 42000,
            'status' => 'activo',
        ]);

        $order = ServiceOrder::create([
            'order_number' => 'OS-LBX-'.fake()->unique()->numerify('####'),
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'mechanic_id' => $mechanic->id,
            'created_by' => $client->id,
            'source' => 'manual',
            'status' => 'en_proceso',
            'progress' => 40,
            'priority' => 'normal',
            'description' => 'Revisión general',
        ]);

        ServicePhoto::create([
            'service_order_id' => $order->id,
            'user_id' => $mechanic->id,
            'type' => 'reception',
            'description' => 'Ingreso a taller',
            'photo_path' => 'service-photos/lightbox-test.jpg',
        ]);

        return [$client, $order];
    }
}
