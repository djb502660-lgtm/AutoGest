<?php

namespace Tests\Unit\Services;

use App\Services\ChatbotAppointmentService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ChatbotAppointmentServiceIntentTest extends TestCase
{
    private ChatbotAppointmentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ChatbotAppointmentService::class);
        $this->startSession();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function appointmentPhrases(): array
    {
        return [
            'agendar' => ['Quiero agendar un servicio'],
            'reservar' => ['Necesito reservar para el lunes'],
            'uppercase' => ['SACAR CITA por favor'],
            'natural' => ['Necesito una cita para revisión de frenos'],
            'turno' => ['turno para mañana'],
        ];
    }

    #[DataProvider('appointmentPhrases')]
    public function test_it_detects_the_intent_to_book_an_appointment(string $text): void
    {
        $this->assertTrue($this->service->wantsAppointment($text));
        $this->assertTrue($this->service->shouldHandle($text));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function managePhrases(): array
    {
        return [
            'menu option' => ['4'],
            'list' => ['ver mis citas'],
            'cancel' => ['quiero cancelar mi cita'],
            'reschedule' => ['reprogramar cita para el viernes'],
            'accented' => ['pásala para el jueves'],
        ];
    }

    #[DataProvider('managePhrases')]
    public function test_it_detects_the_intent_to_manage_existing_appointments(string $text): void
    {
        $this->assertTrue($this->service->wantsManage($text));
        $this->assertTrue($this->service->shouldHandle($text));
    }

    public function test_unrelated_messages_are_not_handled(): void
    {
        foreach (['hola', '¿cuánto gasté este año?', 'estado de mi vehículo ABC-123'] as $text) {
            $this->assertFalse($this->service->wantsAppointment($text), $text);
            $this->assertFalse($this->service->wantsManage($text), $text);
            $this->assertFalse($this->service->shouldHandle($text), $text);
        }
    }

    public function test_an_open_draft_keeps_the_conversation_in_the_appointment_flow(): void
    {
        session()->put('chatbot_appointment_draft', ['step' => 'vehicle']);

        $this->assertTrue($this->service->shouldHandle('mañana a las 10'));
    }

    public function test_an_open_manage_flow_keeps_the_conversation_in_the_appointment_flow(): void
    {
        session()->put('chatbot_appointment_manage', ['step' => 'select']);

        $this->assertTrue($this->service->shouldHandle('la primera'));
    }

    public function test_cancel_draft_clears_the_pending_appointment(): void
    {
        session()->put('chatbot_appointment_draft', ['step' => 'vehicle']);

        $this->service->cancelDraft();

        $this->assertFalse(session()->has('chatbot_appointment_draft'));
        $this->assertFalse($this->service->shouldHandle('mañana a las 10'));
    }
}
