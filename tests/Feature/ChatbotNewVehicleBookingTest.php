<?php

namespace Tests\Feature;

use App\Models\AppointmentRequest;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotNewVehicleBookingTest extends TestCase
{
    use RefreshDatabase;

    private function clientWithVehicle(): User
    {
        $client = User::factory()->client()->create();
        User::factory()->advisor()->create(['status' => 'activo']);
        User::factory()->admin()->create(['status' => 'activo']);

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

    public function test_new_plate_is_registered_and_booked_even_with_an_existing_appointment(): void
    {
        Carbon::setTestNow('2026-08-18 09:00:00');

        $client = $this->clientWithVehicle();

        AppointmentRequest::create([
            'client_id' => $client->id,
            'vehicle_id' => $client->vehicles()->first()->id,
            'requested_date' => '2026-08-21',
            'requested_time' => '10:00:00',
            'service_type' => 'Revisión general',
            'description' => 'Cita previa',
            'status' => 'pendiente',
            'source' => 'chatbot',
        ]);

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'agendar cita'])
            ->assertOk();

        $askBrand = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'PVP-8032 mañana a las 10 cambio de aceite'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('No tienes la placa **PVP-8032** registrada', $askBrand);
        $this->assertStringContainsString('marca y el modelo', $askBrand);

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'Chevrolet Spark 2018'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Registré **PVP-8032**', $reply);
        $this->assertStringContainsString('Solicitud #', $reply);
        $this->assertStringContainsString('Chevrolet Spark', $reply);

        $this->assertDatabaseHas('vehicles', [
            'client_id' => $client->id,
            'plate' => 'PVP-8032',
            'brand' => 'Chevrolet',
            'model' => 'Spark',
            'year' => 2018,
        ]);

        $this->assertDatabaseCount('appointment_requests', 2);
        $this->assertDatabaseHas('appointment_requests', [
            'client_id' => $client->id,
            'source' => 'chatbot',
            'status' => 'pendiente',
            'service_type' => 'Cambio de aceite',
        ]);
        $this->assertTrue(
            AppointmentRequest::query()->where('requested_date', '2026-08-21')->exists(),
            'La cita anterior debe mantenerse'
        );

        Carbon::setTestNow();
    }

    public function test_one_shot_unknown_plate_registers_the_car_and_creates_the_appointment(): void
    {
        Carbon::setTestNow('2026-08-18 09:00:00');

        $client = $this->clientWithVehicle();

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'Quiero agendar cita para GHI-999 mañana a las 10 cambio de aceite',
            ])
            ->assertOk();

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'Nissan Versa 2022'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Registré **GHI-999**', $reply);
        $this->assertStringContainsString('Solicitud #', $reply);
        $this->assertDatabaseHas('vehicles', [
            'client_id' => $client->id,
            'plate' => 'GHI-999',
            'brand' => 'Nissan',
            'model' => 'Versa',
            'year' => 2022,
        ]);
        $this->assertDatabaseCount('appointment_requests', 1);

        Carbon::setTestNow();
    }

    public function test_client_without_vehicles_can_register_a_plate_and_book(): void
    {
        Carbon::setTestNow('2026-08-18 09:00:00');

        $client = User::factory()->client()->create();
        User::factory()->advisor()->create(['status' => 'activo']);
        User::factory()->admin()->create(['status' => 'activo']);

        $greeting = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'hola'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('agendar cita', $greeting);
        $this->assertStringNotContainsString('Contacta al taller para registrarlos', $greeting);

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'agendar cita'])
            ->assertOk();

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'MNO-345'])
            ->assertOk();

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'Kia Rio 2021 mañana a las 10 revisión general'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Registré **MNO-345**', $reply);
        $this->assertStringContainsString('Solicitud #', $reply);
        $this->assertDatabaseHas('vehicles', [
            'client_id' => $client->id,
            'plate' => 'MNO-345',
            'brand' => 'Kia',
            'model' => 'Rio',
        ]);
        $this->assertDatabaseCount('appointment_requests', 1);

        Carbon::setTestNow();
    }

    public function test_typing_a_new_plate_does_not_keep_the_already_selected_vehicle(): void
    {
        $client = $this->clientWithVehicle();

        $first = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'agendar cita'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Toyota Corolla', $first);

        $askBrand = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'XYZ-777'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('No tienes la placa **XYZ-777** registrada', $askBrand);
        $this->assertStringNotContainsString('Vehículo encontrado', $askBrand);
    }
}
