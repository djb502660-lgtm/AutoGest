<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_advisor_dashboard_shows_integrated_calendar(): void
    {
        $advisor = User::factory()->advisor()->create([
            'email' => 'asesor@autogest.test',
        ]);

        $response = $this->actingAs($advisor)
            ->get(route('advisor.dashboard'));

        $response->assertOk();
        $response->assertSee('Agenda del asesor');
    }

    public function test_mechanic_dashboard_shows_integrated_calendar(): void
    {
        $mechanic = User::factory()->mechanic()->create([
            'email' => 'mecanico@autogest.test',
        ]);

        $response = $this->actingAs($mechanic)
            ->get(route('mechanic.dashboard'));

        $response->assertOk();
        $response->assertSee('Agenda del taller');
    }
}
