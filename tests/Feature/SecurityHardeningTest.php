<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('login');
    }

    public function test_login_is_throttled_after_repeated_failed_attempts(): void
    {
        User::factory()->create([
            'email' => 'admin@autogest.test',
            'role' => 'admin',
            'status' => 'activo',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.submit'), [
                'email' => 'admin@autogest.test',
                'password' => 'contrasena-incorrecta',
            ])->assertRedirect();
        }

        $this->post(route('login.submit'), [
            'email' => 'admin@autogest.test',
            'password' => 'contrasena-incorrecta',
        ])->assertStatus(429);
    }

    public function test_chatbot_message_routes_keep_csrf_verification(): void
    {
        foreach (['client.chatbot.message', 'chatbot.message'] as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertContains('web', $route->gatherMiddleware(), "Route [{$name}] must run the web middleware group.");
            $this->assertNotContains(
                VerifyCsrfToken::class,
                $route->excludedMiddleware(),
                "Route [{$name}] must not exclude CSRF verification.",
            );
        }
    }

    public function test_non_client_roles_cannot_use_the_chatbot_endpoint(): void
    {
        foreach (['mecanico', 'asesor'] as $role) {
            $user = User::factory()->create(['role' => $role, 'status' => 'activo']);

            $this->actingAs($user)
                ->postJson(route('chatbot.message'), ['message' => 'hola'])
                ->assertForbidden();
        }
    }
}
