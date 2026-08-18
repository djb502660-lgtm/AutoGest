<?php

namespace Tests\Feature;

use App\Jobs\NotifyAdvisorsOfChatbotQuery;
use App\Models\AppointmentRequest;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ChatbotIntentTest extends TestCase
{
    use RefreshDatabase;

    private function clientWithVehicle(): User
    {
        $client = User::factory()->client()->create(['name' => 'Carlos Pérez']);

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 42000,
            'status' => 'activo',
        ]);

        return $client;
    }

    public function test_hours_and_services_questions_are_answered_without_alerting_advisors(): void
    {
        Bus::fake();

        $client = $this->clientWithVehicle();

        $hours = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => '¿A qué hora abren?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('lunes a viernes', $hours);
        $this->assertStringContainsString('8:00', $hours);

        $services = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'qué servicios ofrecen'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('mantenimiento preventivo', $services);
        $this->assertStringNotContainsString('No encontré una respuesta directa', $services);

        Bus::assertNotDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }

    public function test_loose_datetime_does_not_start_an_appointment(): void
    {
        Bus::fake();
        Carbon::setTestNow('2026-08-17 09:00:00');

        $client = $this->clientWithVehicle();

        $reply = $this->actingAs($client)
            ->withSession([])
            ->postJson(route('client.chatbot.message'), ['message' => 'mañana a las 10'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('¿Quieres agendar una cita', $reply);
        $this->assertDatabaseCount('appointment_requests', 0);
        Bus::assertNotDispatched(NotifyAdvisorsOfChatbotQuery::class);

        Carbon::setTestNow();
    }

    public function test_affirming_a_loose_datetime_starts_the_appointment_flow(): void
    {
        Carbon::setTestNow('2026-08-17 09:00:00');

        $client = $this->clientWithVehicle();

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'mañana a las 10'])
            ->assertOk();

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'sí'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('motivo o tipo de servicio', $reply);
        $this->assertDatabaseCount('appointment_requests', 0);

        Carbon::setTestNow();
    }

    public function test_weekday_alone_does_not_reschedule_an_existing_appointment(): void
    {
        Carbon::setTestNow('2026-08-03 08:00:00');

        $client = $this->clientWithVehicle();

        $appointment = AppointmentRequest::create([
            'client_id' => $client->id,
            'vehicle_id' => $client->vehicles()->first()->id,
            'requested_date' => '2026-08-06',
            'requested_time' => '10:00:00',
            'service_type' => 'Revisión de frenos',
            'description' => 'Ruido al frenar',
            'status' => 'pendiente',
            'source' => 'chatbot',
        ]);

        $reply = $this->actingAs($client)
            ->withSession([])
            ->postJson(route('client.chatbot.message'), ['message' => 'viernes'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('¿Quieres agendar una cita', $reply);

        $appointment->refresh();
        $this->assertSame('2026-08-06', $appointment->requested_date->toDateString());

        Carbon::setTestNow();
    }

    public function test_expense_synonym_still_returns_the_yearly_summary(): void
    {
        Bus::fake();

        $client = $this->clientWithVehicle();

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'quiero el resumen de gastos'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Aún no tienes servicios completados', $reply);
        Bus::assertNotDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }
}
