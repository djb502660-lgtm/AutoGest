<?php

namespace Tests\Feature;

use App\Jobs\NotifyAdvisorsOfChatbotQuery;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ChatbotConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_regional_greetings_get_the_assistant_hello_and_do_not_alert_advisors(): void
    {
        Bus::fake();

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

        foreach (['habla klk', 'klk', 'que mas', 'buenas', 'hola'] as $message) {
            $response = $this->actingAs($client)
                ->postJson(route('client.chatbot.message'), ['message' => $message])
                ->assertOk();

            $reply = $response->json('reply');

            $this->assertStringContainsString('¡Hola Carlos!', $reply, "Expected greeting for «{$message}»");
            $this->assertStringContainsString('AutoGest', $reply);
            $this->assertStringContainsString('Toyota Corolla', $reply);
            $this->assertStringNotContainsString('No encontré una respuesta directa', $reply);
        }

        Bus::assertNotDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }

    public function test_thanks_farewell_and_help_do_not_alert_advisors(): void
    {
        Bus::fake();

        $client = User::factory()->client()->create(['name' => 'Carlos Pérez']);

        $cases = [
            'gracias' => '¡Con gusto Carlos!',
            'chao' => '¡Hasta luego Carlos!',
            'ayuda' => 'Puedo ayudarte con esto',
        ];

        foreach ($cases as $message => $expected) {
            $reply = $this->actingAs($client)
                ->postJson(route('client.chatbot.message'), ['message' => $message])
                ->assertOk()
                ->json('reply');

            $this->assertStringContainsString($expected, $reply);
            $this->assertStringNotContainsString('No encontré una respuesta directa', $reply);
        }

        Bus::assertNotDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }

    public function test_greeting_mixed_with_vehicle_status_still_answers_the_vehicle(): void
    {
        Bus::fake();

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

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'hola, estado de mi auto',
            ])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Tu Toyota Corolla (ABC-123) está Activo.', $reply);

        Bus::assertNotDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }

    public function test_unmatched_workshop_question_still_alerts_advisors(): void
    {
        Bus::fake();

        $client = User::factory()->client()->create();

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'el motor hace un ruido raro al frenar',
            ])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('No encontré una respuesta directa', $reply);
        Bus::assertDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }
}
