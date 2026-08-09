<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_exit_without_enough_inventory_returns_unprocessable_and_keeps_stock(): void
    {
        $admin = User::factory()->admin()->create();

        $product = Product::create([
            'name' => 'Pastillas de freno',
            'sku' => 'ERR-001',
            'purchase_price' => 10,
            'sale_price' => 20,
            'stock_quantity' => 2,
            'min_stock' => 1,
            'unit' => 'unid',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->postJson(route('stock.store'), [
            'product_id' => $product->id,
            'tipo_movimiento' => 'egreso',
            'quantity' => 5,
            'motivo' => 'Salida mayor al stock disponible',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertSame(2, $product->fresh()->stock_quantity);
        $this->assertSame(0, StockMovement::where('product_id', $product->id)->count());
    }

    public function test_chatbot_message_failure_returns_server_error_with_fallback_reply(): void
    {
        $client = User::factory()->client()->create();

        $chatbot = $this->mock(ChatbotService::class);
        $chatbot->shouldReceive('processMessage')
            ->once()
            ->andThrow(new RuntimeException('fallo interno del chatbot'));

        $response = $this->actingAs($client)
            ->postJson(route('client.chatbot.message'), ['message' => 'hola']);

        $response->assertStatus(500)
            ->assertJsonPath('error', true);

        $this->assertNotEmpty($response->json('reply'));
    }

    public function test_report_email_failure_is_reported_to_the_user_and_not_logged_as_sent(): void
    {
        $admin = User::factory()->admin()->create();

        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new RuntimeException('SMTP no disponible'));

        $response = $this->actingAs($admin)->post(route('reports.email'), [
            'type' => 'vehiculos',
        ]);

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('activity_logs', ['action' => 'report.emailed']);
    }
}
