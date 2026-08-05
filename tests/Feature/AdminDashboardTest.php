<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@autogest.test',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Sistema administrativo');
        $response->assertSee('Órdenes de servicio recientes');
        $response->assertSee('Agenda operativa');
    }
}
