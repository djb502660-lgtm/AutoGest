<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisibleLayoutSmokeTest extends TestCase
{
    use RefreshDatabase;


    public function test_login_page_loads_vite_assets_without_conflict_markers(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Inicia sesión');
        $response->assertSee('resources/css/app.css');
        $response->assertDontSee('<<<<<<<');
        $response->assertDontSee('frontend/css/app.css');
    }

    public function test_admin_dashboard_renders_layout_and_widgets(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Sistema administrativo');
        $response->assertSee('stats-grid', false);
        $response->assertSee('panel-grid', false);
        $response->assertSee('chart-bars', false);
        $response->assertSee('bar-track', false);
        $response->assertSee('Órdenes de servicio recientes');
        $response->assertSee('Resumen operativo');
        $response->assertSee('class="shell"', false);
        $response->assertSee('desktop-sidebar', false);
        $response->assertSee('class="menu-item', false);
        $response->assertDontSee('nav-link');
        $response->assertDontSee('<<<<<<<');
    }

    public function test_advisor_dashboard_renders(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this->actingAs($advisor)->get(route('advisor.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard principal');
        $response->assertSee('class="shell"', false);
        $response->assertSee('desktop-sidebar', false);
    }

    public function test_mechanic_dashboard_renders(): void
    {
        $mechanic = User::factory()->mechanic()->create();

        $response = $this->actingAs($mechanic)->get(route('mechanic.dashboard'));

        $response->assertOk();
        $response->assertSee('Panel de Trabajo del Mecánico');
        $response->assertSee('class="shell"', false);
    }

    public function test_client_dashboard_renders(): void
    {
        $client = User::factory()->client()->create();

        $response = $this->actingAs($client)->get(route('client.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard principal');
        $response->assertSee('class="shell"', false);
        $response->assertSee('desktop-sidebar', false);
        $response->assertSee('chatbot-fab', false);
        $response->assertSee('chatbot-field', false);
        $response->assertSee('Asistente');
    }

    public function test_client_chatbot_page_matches_panel_ui(): void
    {
        $client = User::factory()->client()->create();

        $response = $this->actingAs($client)->get(route('client.chatbot.index'));

        $response->assertOk();
        $response->assertSee('AutoGest Bot');
        $response->assertSee('chatbot-page', false);
        $response->assertSee('chatbot-room', false);
        $response->assertSee('Escribe tu pregunta');
        $response->assertDontSee('rgba(8, 15, 29');
        $response->assertDontSee('<<<<<<<');
    }

    public function test_role_home_routes_redirect_after_login(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin-smoke@autogest.test',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_https_app_url_forces_https_links(): void
    {
        config(['app.url' => 'https://autogest.example.test']);
        (new AppServiceProvider(app()))->boot();

        $this->assertSame('https://autogest.example.test/login', url('/login'));
        $this->assertSame('https://autogest.example.test/build/assets/app.css', asset('build/assets/app.css'));
    }
}
