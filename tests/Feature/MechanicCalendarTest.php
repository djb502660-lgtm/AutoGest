<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MechanicCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_mechanic_can_access_calendar(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Mechanic,
            'email' => 'mecanico@autogest.test',
        ]);

        $response = $this->actingAs($user)
            ->get(route('mechanic.calendar.index'));

        $response->assertOk();
        $response->assertSee('Calendario de trabajo');
    }
}
