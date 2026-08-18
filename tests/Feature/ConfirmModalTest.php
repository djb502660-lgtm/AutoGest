<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_layout_includes_the_shared_confirm_modal(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="confirmModal"', false)
            ->assertSee('AutoGestConfirm', false);
    }

    public function test_admin_delete_actions_use_data_confirm_instead_of_native_confirm(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->advisor()->create();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="Eliminar usuario"', false)
            ->assertSee('data-confirm=', false)
            ->assertDontSee("onsubmit=\"return confirm(", false);
    }

    public function test_mechanic_photo_delete_uses_shared_confirm_modal(): void
    {
        $mechanic = User::factory()->mechanic()->create();
        $client = User::factory()->client()->create();

        $vehicle = \App\Models\Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'CNF-001',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 10000,
            'status' => 'activo',
        ]);

        $order = \App\Models\ServiceOrder::create([
            'order_number' => 'OS-CNF-0001',
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'mechanic_id' => $mechanic->id,
            'created_by' => $client->id,
            'source' => 'manual',
            'status' => 'en_proceso',
            'progress' => 20,
            'priority' => 'normal',
            'description' => 'Prueba confirmación',
        ]);

        $this->actingAs($mechanic)
            ->get(route('mechanic.orders.show', $order))
            ->assertOk()
            ->assertSee('AutoGestConfirm.ask', false)
            ->assertDontSee("confirm('¿Eliminar esta foto?')", false)
            ->assertSee('id="photoUploadModal"', false)
            ->assertSee('Nueva evidencia fotográfica');
    }
}
