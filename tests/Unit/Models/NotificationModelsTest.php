<?php

namespace Tests\Unit\Models;

use App\Models\Alert;
use App\Models\ChatbotMessage;
use App\Models\OrderComment;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Tests\TestCase;

class NotificationModelsTest extends TestCase
{
    public function test_alert_casts_flags_and_dates(): void
    {
        $casts = (new Alert)->getCasts();

        $this->assertSame('date', $casts['due_date']);
        $this->assertSame('boolean', $casts['is_read']);
        $this->assertSame('boolean', $casts['is_resolved']);
        $this->assertSame('datetime', $casts['resolved_at']);
    }

    public function test_alert_belongs_to_a_vehicle_and_a_user(): void
    {
        $alert = new Alert;

        $this->assertInstanceOf(Vehicle::class, $alert->vehicle()->getRelated());
        $this->assertInstanceOf(User::class, $alert->user()->getRelated());
    }

    public function test_chatbot_message_metadata_is_cast_to_an_array(): void
    {
        $message = new ChatbotMessage;
        $message->setRawAttributes(['metadata' => json_encode(['intent' => 'cita'])]);

        $this->assertSame('array', $message->getCasts()['metadata']);
        $this->assertSame(['intent' => 'cita'], $message->metadata);
        $this->assertInstanceOf(User::class, $message->user()->getRelated());
    }

    public function test_order_comment_belongs_to_its_order_and_author(): void
    {
        $comment = new OrderComment;

        $this->assertInstanceOf(ServiceOrder::class, $comment->serviceOrder()->getRelated());
        $this->assertInstanceOf(User::class, $comment->user()->getRelated());
        $this->assertSame(['service_order_id', 'user_id', 'comment'], $comment->getFillable());
    }
}
