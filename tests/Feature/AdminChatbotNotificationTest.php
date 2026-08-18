<?php

namespace Tests\Feature;

use App\Models\AppointmentRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminChatbotNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_lists_chatbot_appointment_notifications(): void
    {
        $admin = User::factory()->admin()->create(['status' => 'activo']);
        $client = User::factory()->client()->create();
        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
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

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Notificaciones del chatbot')
            ->assertSee('Solicitudes chatbot')
            ->assertSee('Cambio de aceite')
            ->assertSee('ABC-123');
    }

    public function test_admin_inbox_shows_chatbot_alert_after_client_books(): void
    {
        $admin = User::factory()->admin()->create(['status' => 'activo']);
        User::factory()->advisor()->create(['status' => 'activo']);
        $client = User::factory()->client()->create();

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 10000,
            'status' => 'activo',
        ]);

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'Quiero agendar cita para ABC123 mañana a las 10 cambio de aceite',
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.chatbot-appointments.index'))
            ->assertOk()
            ->assertSee('Nueva solicitud de cita (chatbot)')
            ->assertSee('Cambio de aceite')
            ->assertSee('Pendientes (1)', false);
    }

    public function test_admin_inbox_hides_converted_requests_from_pending_and_links_the_alert(): void
    {
        $admin = User::factory()->admin()->create(['status' => 'activo']);
        $client = User::factory()->client()->create(['name' => 'Adrian Arboleda']);
        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'PVP-8032',
            'brand' => 'Kia',
            'model' => 'Rio',
            'year' => 2022,
            'mileage' => 15000,
            'status' => 'activo',
        ]);

        $appointment = AppointmentRequest::create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'requested_date' => '2026-08-21',
            'requested_time' => '10:00:00',
            'service_type' => 'General',
            'description' => 'Chatbot',
            'status' => 'pendiente',
            'source' => 'chatbot',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.chatbot-appointments.confirm', $appointment), [
                'advisor_notes' => 'Confirmada desde admin',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('appointment_requests', [
            'id' => $appointment->id,
            'status' => 'convertida',
        ]);

        $this->assertDatabaseHas('service_orders', [
            'client_id' => $client->id,
            'source' => 'chatbot',
            'created_by' => $admin->id,
            'advisor_id' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.chatbot-appointments.index'))
            ->assertOk()
            ->assertSee('No hay solicitudes con ese filtro.')
            ->assertSee('Hay 1 convertida(s) a orden.')
            ->assertSee('Ver todas')
            ->assertDontSee('>General<', false);

        $this->actingAs($admin)
            ->get(route('admin.chatbot-appointments.index', ['status' => 'todas']))
            ->assertOk()
            ->assertSee('PVP-8032')
            ->assertSee('Convertida a orden');
    }

    public function test_chatbot_booking_alert_points_to_the_admin_request_show_page(): void
    {
        $admin = User::factory()->admin()->create(['status' => 'activo']);
        User::factory()->advisor()->create(['status' => 'activo']);
        $client = User::factory()->client()->create();

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 10000,
            'status' => 'activo',
        ]);

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'Quiero agendar cita para ABC123 mañana a las 10 cambio de aceite',
            ])
            ->assertOk();

        $appointment = AppointmentRequest::query()->latest('id')->first();
        $this->assertNotNull($appointment);
        $this->assertDatabaseHas('alerts', [
            'user_id' => $admin->id,
            'appointment_request_id' => $appointment->id,
            'title' => 'Nueva solicitud de cita (chatbot)',
            'is_read' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.chatbot-appointments.index'))
            ->assertOk()
            ->assertSee(route('admin.chatbot-appointments.show', $appointment), false);
    }
}
