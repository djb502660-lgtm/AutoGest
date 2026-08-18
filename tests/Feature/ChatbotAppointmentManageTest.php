<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\AppointmentRequest;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotAppointmentManageTest extends TestCase
{
    use RefreshDatabase;

    private function seedClientWithAppointment(): array
    {
        $client = User::factory()->client()->create();
        $advisor = User::factory()->advisor()->create(['status' => 'activo']);
        $admin = User::factory()->admin()->create(['status' => 'activo']);

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Chevrolet',
            'model' => 'Aveo',
            'year' => 2020,
            'mileage' => 42000,
            'status' => 'activo',
        ]);

        $appointment = AppointmentRequest::create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'requested_date' => '2026-08-06',
            'requested_time' => '10:00:00',
            'service_type' => 'Revisión de frenos',
            'description' => 'Ruido al frenar',
            'status' => 'pendiente',
            'source' => 'chatbot',
        ]);

        return compact('client', 'advisor', 'admin', 'vehicle', 'appointment');
    }

    public function test_chatbot_lists_active_appointment(): void
    {
        ['client' => $client] = $this->seedClientWithAppointment();

        $response = $this->actingAs($client)
            ->withSession([])
            ->postJson(route('client.chatbot.message'), ['message' => '¿Tengo alguna cita?']);

        $response->assertOk();
        $this->assertStringContainsString('cita registrada', $response->json('reply'));
        $this->assertStringContainsString('Revisión de frenos', $response->json('reply'));
        $this->assertStringContainsString('Pendiente de confirmación', $response->json('reply'));
    }

    public function test_chatbot_cancels_appointment_after_confirmation(): void
    {
        ['client' => $client, 'advisor' => $advisor, 'admin' => $admin, 'appointment' => $appointment] = $this->seedClientWithAppointment();

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'Quiero cancelar mi cita'])
            ->assertOk();

        $confirm = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'Sí']);

        $confirm->assertOk();
        $this->assertStringContainsString('cancelada correctamente', $confirm->json('reply'));

        $this->assertDatabaseHas('appointment_requests', [
            'id' => $appointment->id,
            'status' => 'cancelada',
        ]);

        $this->assertDatabaseHas('alerts', [
            'user_id' => $advisor->id,
            'vehicle_id' => $appointment->vehicle_id,
            'title' => 'Cita cancelada por cliente (chatbot)',
        ]);
        $this->assertDatabaseHas('alerts', [
            'user_id' => $admin->id,
            'vehicle_id' => $appointment->vehicle_id,
            'title' => 'Cita cancelada por cliente (chatbot)',
        ]);
    }

    public function test_chatbot_keeps_appointment_when_cancel_is_declined(): void
    {
        ['client' => $client, 'appointment' => $appointment] = $this->seedClientWithAppointment();

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'cancelar mi cita']);

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'No'])
            ->assertOk();

        $this->assertDatabaseHas('appointment_requests', [
            'id' => $appointment->id,
            'status' => 'pendiente',
        ]);
    }

    public function test_chatbot_reschedules_appointment_time_from_context(): void
    {
        Carbon::setTestNow('2026-08-03 08:00:00');

        ['client' => $client, 'advisor' => $advisor, 'admin' => $admin, 'appointment' => $appointment] = $this->seedClientWithAppointment();

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => '¿Tengo alguna cita?']);

        $response = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'Mejor pásala para la tarde']);

        $response->assertOk();
        $this->assertStringContainsString('actualizada correctamente', $response->json('reply'));

        $this->assertDatabaseHas('appointment_requests', [
            'id' => $appointment->id,
            'requested_time' => '15:00:00',
            'status' => 'pendiente',
        ]);

        $this->assertTrue(
            Alert::where('user_id', $advisor->id)
                ->where('title', 'Cita actualizada por cliente (chatbot)')
                ->exists()
        );
        $this->assertTrue(
            Alert::where('user_id', $admin->id)
                ->where('title', 'Cita actualizada por cliente (chatbot)')
                ->exists()
        );

        Carbon::setTestNow();
    }

    public function test_chatbot_reschedules_appointment_date_and_prompts_for_time(): void
    {
        Carbon::setTestNow('2026-08-03 08:00:00');

        ['client' => $client, 'appointment' => $appointment] = $this->seedClientWithAppointment();

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'Quiero cambiar mi cita']);

        $response = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'viernes']);

        $response->assertOk();
        $this->assertStringContainsString('disponibilidad', $response->json('reply'));

        $appointment->refresh();
        $this->assertSame('2026-08-07', $appointment->requested_date->toDateString());

        Carbon::setTestNow();
    }

    public function test_chatbot_shows_appointment_history(): void
    {
        ['client' => $client, 'vehicle' => $vehicle] = $this->seedClientWithAppointment();

        AppointmentRequest::create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'requested_date' => '2026-06-10',
            'requested_time' => '09:00:00',
            'service_type' => 'Cambio de aceite',
            'description' => 'Mantenimiento',
            'status' => 'convertida',
            'source' => 'chatbot',
        ]);

        $response = $this->actingAs($client)
            ->withSession([])
            ->postJson(route('client.chatbot.message'), ['message' => 'Muéstrame todas mis citas']);

        $response->assertOk();
        $this->assertStringContainsString('historial de citas', $response->json('reply'));
        $this->assertStringContainsString('Cambio de aceite', $response->json('reply'));
        $this->assertStringContainsString('Revisión de frenos', $response->json('reply'));
    }

    public function test_chatbot_deletes_appointment_with_eliminar_and_notifies_staff(): void
    {
        ['client' => $client, 'advisor' => $advisor, 'admin' => $admin, 'appointment' => $appointment] = $this->seedClientWithAppointment();

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'eliminar la cita'])
            ->assertOk();

        $confirm = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'Sí'])
            ->assertOk();

        $this->assertStringContainsString('cancelada correctamente', $confirm->json('reply'));
        $this->assertDatabaseHas('appointment_requests', [
            'id' => $appointment->id,
            'status' => 'cancelada',
        ]);
        $this->assertDatabaseHas('alerts', [
            'user_id' => $advisor->id,
            'title' => 'Cita cancelada por cliente (chatbot)',
        ]);
        $this->assertDatabaseHas('alerts', [
            'user_id' => $admin->id,
            'title' => 'Cita cancelada por cliente (chatbot)',
        ]);
    }

    // ELIMINADO: test_chatbot_guided_brake_symptom_flow
    // Funcionalidad de diagnóstico de síntomas eliminada del chatbot (Sprint 5B)
    // El chatbot ahora solo maneja gestión básica de citas y estado de vehículos
}
