<?php

namespace Tests\Feature;

use App\Jobs\NotifyAdvisorsOfChatbotQuery;
use App\Models\ChatbotFaq;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ChatbotFaqTest extends TestCase
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

    private function seedWorkshopFaqs(): void
    {
        ChatbotFaq::create([
            'category' => 'General',
            'question' => '¿Cuál es el horario del taller?',
            'answer' => 'Horario FAQ: lunes a viernes de 8:00 a 18:00 y sábados de 8:00 a 13:00.',
            'keywords' => 'horario,taller,atención',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ChatbotFaq::create([
            'category' => 'Servicios',
            'question' => '¿Qué servicios ofrecen?',
            'answer' => 'Servicios FAQ: mantenimiento preventivo y correctivo en el taller.',
            'keywords' => 'servicios,mantenimiento,preventivo,correctivo',
            'is_active' => true,
            'sort_order' => 2,
        ]);
    }

    public function test_hours_and_services_answers_come_from_chatbot_faqs(): void
    {
        Bus::fake();
        $this->seedWorkshopFaqs();
        $client = $this->clientWithVehicle();

        $hours = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => '¿A qué hora abren?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Horario FAQ', $hours);

        $services = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'qué servicios ofrecen'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Servicios FAQ', $services);
        Bus::assertNotDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }

    public function test_keyword_faq_match_answers_without_alerting_advisors(): void
    {
        Bus::fake();
        $this->seedWorkshopFaqs();

        ChatbotFaq::create([
            'category' => 'Pagos',
            'question' => '¿Aceptan tarjeta?',
            'answer' => 'Sí, aceptamos tarjeta y efectivo.',
            'keywords' => 'tarjeta,efectivo,pago',
            'is_active' => true,
            'sort_order' => 5,
        ]);

        $client = $this->clientWithVehicle();

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'aceptan tarjeta'])
            ->assertOk()
            ->json('reply');

        $this->assertSame('Sí, aceptamos tarjeta y efectivo.', $reply);
        Bus::assertNotDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }

    public function test_inactive_faq_is_ignored_and_business_query_still_alerts(): void
    {
        Bus::fake();

        ChatbotFaq::create([
            'category' => 'Pagos',
            'question' => '¿Aceptan tarjeta?',
            'answer' => 'Esta respuesta no debe usarse.',
            'keywords' => 'tarjeta,efectivo,pago',
            'is_active' => false,
            'sort_order' => 5,
        ]);

        $client = $this->clientWithVehicle();

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'aceptan tarjeta'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('No encontré una respuesta directa', $reply);
        Bus::assertDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }

    public function test_vehicle_status_is_not_replaced_by_the_faq_about_how_to_consult(): void
    {
        Bus::fake();
        $this->seedWorkshopFaqs();

        ChatbotFaq::create([
            'category' => 'Mantenimiento',
            'question' => '¿Cómo consulto el estado de mi vehículo?',
            'answer' => 'Pregúntame la placa. Esta FAQ no debe sustituir el estado real.',
            'keywords' => 'estado,vehículo,placa,seguimiento',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $client = $this->clientWithVehicle();

        $reply = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'estado de mi auto'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Tu Toyota Corolla (ABC-123) está Activo.', $reply);

        Bus::assertNotDispatched(NotifyAdvisorsOfChatbotQuery::class);
    }
}
