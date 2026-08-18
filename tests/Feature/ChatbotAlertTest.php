<?php

namespace Tests\Feature;

use App\Jobs\NotifyAdvisorsOfChatbotQuery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ChatbotAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_dispatches_job_when_workshop_query_has_no_faq_match(): void
    {
        Bus::fake();

        $client = User::factory()->client()->create();

        $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), [
                'message' => 'el motor hace un ruido raro al frenar',
            ])
            ->assertOk();

        Bus::assertDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }
}
