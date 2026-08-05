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

    // ELIMINADO: test_chatbot_dispatches_job_when_query_has_no_faq_match
    // Funcionalidad de FAQ eliminada del chatbot (Sprint 5B)
    // El chatbot ahora usa lógica directa sin sistema de FAQ
}
