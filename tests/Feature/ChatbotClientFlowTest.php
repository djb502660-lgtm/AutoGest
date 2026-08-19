<?php

namespace Tests\Feature;

use App\Models\AppointmentRequest;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotClientFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_can_consult_vehicle_status_with_plate_without_separator(): void
    {
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 42000,
            'status' => 'en_taller',
        ]);

        ServiceOrder::create([
            'order_number' => 'OS-2026-0401',
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'created_by' => $client->id,
            'source' => 'manual',
            'status' => 'en_proceso',
            'progress' => 65,
            'priority' => 'normal',
            'description' => 'Diagnóstico eléctrico',
        ]);

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'Quiero consultar el estado del ABC123',
            ])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString(
            'Tu Toyota Corolla (ABC-123) está En taller. Última orden: Diagnóstico eléctrico — En proceso.',
            $reply
        );
        $this->assertStringContainsString('Kilometraje:', $reply);
    }

    public function test_chatbot_can_consult_vehicle_status_without_plate_when_client_has_single_vehicle(): void
    {
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 42000,
            'status' => 'en_taller',
        ]);

        ServiceOrder::create([
            'order_number' => 'OS-2026-0401',
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'created_by' => $client->id,
            'source' => 'manual',
            'status' => 'en_proceso',
            'progress' => 65,
            'priority' => 'normal',
            'description' => 'Diagnóstico eléctrico',
        ]);

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'Quiero consultar el estado de mi auto',
            ])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString(
            'Tu Toyota Corolla (ABC-123) está En taller. Última orden: Diagnóstico eléctrico — En proceso.',
            $reply
        );
    }

    public function test_chatbot_provides_open_vehicle_information_when_client_has_multiple_vehicles_and_no_plate(): void
    {
        $client = User::factory()->client()->create();

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 42000,
            'status' => 'en_taller',
        ]);

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'XYZ-987',
            'brand' => 'Nissan',
            'model' => 'Sentra',
            'year' => 2022,
            'mileage' => 18000,
            'status' => 'activo',
        ]);

        $response = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'Quiero consultar el estado de mi vehículo',
            ]);

        $response->assertOk();
        $this->assertStringContainsString('Tienes 2 vehículos registrados', $response->json('reply'));
        $this->assertStringContainsString('(ABC-123) está En taller', $response->json('reply'));
        $this->assertStringContainsString('(XYZ-987) está Activo', $response->json('reply'));
    }

    public function test_chatbot_can_create_appointment_with_plate_without_separator(): void
    {
        $client = User::factory()->client()->create();
        User::factory()->advisor()->create(['status' => 'activo']);

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 42000,
            'status' => 'activo',
        ]);

        $response = $this->actingAs($client)
            ->withSession([])
            ->postJson(route('client.chatbot.message'), [
                'message' => 'Quiero agendar cita para ABC123 mañana a las 10 cambio de aceite',
            ]);

        $response->assertOk();
        $this->assertStringContainsString('Solicitud #', $response->json('reply'));
        $this->assertStringContainsString('registrada para el', $response->json('reply'));

        $this->assertDatabaseCount('appointment_requests', 1);

        $appointment = AppointmentRequest::query()->first();

        $this->assertNotNull($appointment);
        $this->assertSame('chatbot', $appointment->source);
        $this->assertSame('pendiente', $appointment->status);
        $this->assertSame('Cambio de aceite', $appointment->service_type);
    }

    public function test_chatbot_understands_natural_vehicle_query_phrase(): void
    {
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'XYZ-987',
            'brand' => 'Nissan',
            'model' => 'Sentra',
            'year' => 2022,
            'mileage' => 18000,
            'status' => 'activo',
        ]);

        ServiceOrder::create([
            'order_number' => 'OS-2026-0402',
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'created_by' => $client->id,
            'source' => 'manual',
            'status' => 'recibida',
            'progress' => 0,
            'priority' => 'normal',
            'description' => 'Chequeo general',
        ]);

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'consulta mi auto xyz987',
            ])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString(
            'Tu Nissan Sentra (XYZ-987) está Activo. Última orden: Chequeo general — Recibida.',
            $reply
        );
    }

    public function test_chatbot_answers_plate_only_vehicle_status_after_general_query(): void
    {
        $client = User::factory()->client()->create();

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 42000,
            'status' => 'activo',
        ]);

        $secondVehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'DEF-456',
            'brand' => 'Hyundai',
            'model' => 'Tucson',
            'year' => 2020,
            'mileage' => 51000,
            'status' => 'en_taller',
        ]);

        ServiceOrder::create([
            'order_number' => 'OS-2026-0403',
            'vehicle_id' => $secondVehicle->id,
            'client_id' => $client->id,
            'created_by' => $client->id,
            'source' => 'manual',
            'status' => 'en_proceso',
            'progress' => 20,
            'priority' => 'normal',
            'description' => 'Revisión frenos',
        ]);

        $response = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'quiero consultar el estado de mi auto'])
            ->assertOk();

        $this->assertStringContainsString('DEF-456', $response->json('reply'));

        $detail = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'DEF-456'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString(
            'Tu Hyundai Tucson (DEF-456) está En taller. Última orden: Revisión frenos — En proceso.',
            $detail
        );
    }

    public function test_chatbot_selects_vehicle_by_brand_when_scheduling(): void
    {
        $client = User::factory()->client()->create();
        User::factory()->advisor()->create(['status' => 'activo']);

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 42000,
            'status' => 'activo',
        ]);

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'DEF-456',
            'brand' => 'Hyundai',
            'model' => 'Tucson',
            'year' => 2020,
            'mileage' => 51000,
            'status' => 'activo',
        ]);

        $ask = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'quiero una cita'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('DEF-456', $ask);
        $this->assertStringContainsString('Hyundai', $ask);

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'la Hyundai'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Hyundai Tucson', $reply);
        $this->assertStringContainsString('fecha', mb_strtolower($reply));
        $this->assertStringNotContainsString('Indícame la placa', $reply);
    }

    // ELIMINADO: test_chatbot_suggests_next_available_date_when_day_is_full
    // ELIMINADO: test_chatbot_uses_current_year_for_day_month_and_requests_future_date_if_already_passed
    // ELIMINADO: test_chatbot_respects_explicit_full_year_date_and_uses_the_same_year
    // Lógica compleja de disponibilidad y fechas eliminada del chatbot (Sprint 5B)
    // El chatbot ahora usa agendamiento simple sin validación de disponibilidad compleja

    // ELIMINADO: test_chatbot_can_schedule_using_weekday_and_simple_hour_phrase
    // ELIMINADO: test_chatbot_understands_this_friday_phrase
    // ELIMINADO: test_chatbot_understands_next_monday_phrase
    // ELIMINADO: test_chatbot_assigns_afternoon_default_time_when_user_says_tomorrow_in_afternoon
    // Lógica de programación con lenguaje natural eliminada del chatbot (Sprint 5B)
    // El chatbot ahora usa fechas explícitas sin procesamiento de lenguaje natural complejo
}
