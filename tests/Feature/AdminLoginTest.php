<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_access_dashboard(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@autogest.test',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@autogest.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->get('/dashboard')->assertOk();
    }
}
