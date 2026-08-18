<?php

namespace Tests\Feature;

use App\Jobs\NotifyAdvisorsOfChatbotQuery;
use App\Models\Alert;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ChatbotEvalBankTest extends TestCase
{
    use RefreshDatabase;

    private function seededClient(): User
    {
        $client = User::factory()->client()->create(['name' => 'Carlos Pérez']);
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

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function smallTalkProvider(): array
    {
        return [
            'hola' => ['hola', '¡Hola Carlos!'],
            'klk' => ['klk', '¡Hola Carlos!'],
            'habla' => ['habla', '¡Hola Carlos!'],
            'habla klk' => ['habla klk', '¡Hola Carlos!'],
            'gracias' => ['gracias', '¡Con gusto Carlos!'],
        ];
    }

    #[DataProvider('smallTalkProvider')]
    public function test_small_talk_does_not_escalate(string $message, string $expected): void
    {
        Bus::fake();

        $reply = $this->actingAs($this->seededClient())
            ->postJson(route('client.chatbot.message'), ['message' => $message])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString($expected, $reply);
        $this->assertStringNotContainsString('No encontré una respuesta directa', $reply);
        Bus::assertNotDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }

    public function test_plate_returns_vehicle_status_without_escalating(): void
    {
        Bus::fake();

        $reply = $this->actingAs($this->seededClient())
            ->postJson(route('client.chatbot.message'), [
                'message' => 'Quiero consultar el estado del ABC123',
            ])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Tu Toyota Corolla (ABC-123) está Activo.', $reply);
        $this->assertStringContainsString('Año: 2021', $reply);
        $this->assertStringContainsString('Kilometraje:', $reply);

        Bus::assertNotDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }

    public function test_appointment_request_is_created_from_natural_phrase(): void
    {
        Bus::fake();

        $client = $this->seededClient();

        $reply = $this->actingAs($client)
            ->withSession([])
            ->postJson(route('client.chatbot.message'), [
                'message' => 'Quiero agendar cita para ABC123 mañana a las 10 cambio de aceite',
            ])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Solicitud #', $reply);
        $this->assertStringContainsString('registrada para el', $reply);
        $this->assertDatabaseCount('appointment_requests', 1);
        $this->assertDatabaseHas('appointment_requests', [
            'client_id' => $client->id,
            'source' => 'chatbot',
            'status' => 'pendiente',
            'service_type' => 'Cambio de aceite',
        ]);
        $this->assertSame(2, Alert::where('title', 'Nueva solicitud de cita (chatbot)')->count());
        Bus::assertNotDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }

    public function test_unknown_plate_during_booking_lists_the_clients_vehicles(): void
    {
        $client = $this->seededClient();

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'DEF-456',
            'brand' => 'Hyundai',
            'model' => 'Tucson',
            'year' => 2019,
            'mileage' => 10000,
            'status' => 'activo',
        ]);

        $ask = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'creame una reserva',
            ])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Indícame la placa', $ask);
        $this->assertStringContainsString('ABC-123', $ask);
        $this->assertStringContainsString('DEF-456', $ask);

        $unknown = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'PVP-7506',
            ])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('No tienes la placa **PVP-7506** registrada', $unknown);
        $this->assertStringContainsString('marca y el modelo', $unknown);

        $registered = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'Kia Rio 2021',
            ])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Registré **PVP-7506**', $registered);
        $this->assertStringContainsString('Kia Rio', $registered);
        $this->assertDatabaseHas('vehicles', [
            'client_id' => $client->id,
            'plate' => 'PVP-7506',
            'brand' => 'Kia',
            'model' => 'Rio',
            'year' => 2021,
        ]);
    }

    public function test_unmatched_workshop_question_does_escalate(): void
    {
        Bus::fake();

        $reply = $this->actingAs($this->seededClient())
            ->postJson(route('client.chatbot.message'), [
                'message' => 'el motor hace un ruido raro al frenar',
            ])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('No encontré una respuesta directa', $reply);
        Bus::assertDispatched(NotifyAdvisorsOfChatbotQuery::class, function (NotifyAdvisorsOfChatbotQuery $job) {
            return str_contains($job->query, 'ruido raro');
        });
    }
}
