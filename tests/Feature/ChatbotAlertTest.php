<?php

namespace Tests\Feature;

use App\Jobs\NotifyAdvisorsOfChatbotQuery;
use App\Models\ChatbotMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ChatbotAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_dispatches_job_when_query_has_no_faq_match(): void
    {
        Bus::fake();

        $client = User::factory()->client()->create();
        User::factory()->advisor()->create(['status' => 'activo']);

        $this->actingAs($client)
            ->post('/cliente/chatbot/mensaje', ['message' => 'Necesito ayuda con un problema extraño en el motor'])
            ->assertJson(['reply' => 'No encontré una respuesta directa para eso. Un asesor de servicio revisará tu consulta y te contactará pronto.']);

        $this->assertDatabaseHas('chatbot_messages', [
            'user_id' => $client->id,
            'sender' => 'user',
            'message' => 'Necesito ayuda con un problema extraño en el motor',
        ]);

        $this->assertDatabaseHas('chatbot_messages', [
            'user_id' => $client->id,
            'sender' => 'bot',
            'message' => 'No encontré una respuesta directa para eso. Un asesor de servicio revisará tu consulta y te contactará pronto.',
        ]);

        Bus::assertDispatched(NotifyAdvisorsOfChatbotQuery::class, function ($job) use ($client) {
            return $job->client->is($client)
                && $job->query === 'Necesito ayuda con un problema extraño en el motor';
        });
    }
}
