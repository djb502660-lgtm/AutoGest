<?php

namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Maintenance;
use App\Models\ServicePhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_view_service_timeline(): void
    {
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 42000,
            'status' => 'activo',
        ]);

        $order = ServiceOrder::create([
            'order_number' => 'OS-2026-0401',
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'created_by' => $client->id,
            'source' => 'manual',
            'status' => 'en_proceso',
            'progress' => 65,
            'priority' => 'normal',
            'description' => 'Diagnóstico eléctrico',
            'started_at' => now()->subHours(2),
        ]);

        $response = $this->actingAs($client)
            ->getJson(route('client.orders.timeline', $order));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'timeline' => [
                'order_id' => $order->id,
                'order_number' => 'OS-2026-0401',
                'status' => 'en_proceso',
            ],
        ]);

        $timeline = $response->json('timeline');
        $this->assertArrayHasKey('events', $timeline);
        $this->assertArrayHasKey('maintenances', $timeline);
        $this->assertArrayHasKey('photos', $timeline);
    }

    public function test_timeline_generates_events_from_order_status(): void
    {
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 42000,
            'status' => 'activo',
        ]);

        $order = ServiceOrder::create([
            'order_number' => 'OS-2026-0402',
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'created_by' => $client->id,
            'source' => 'manual',
            'status' => 'completada',
            'progress' => 100,
            'priority' => 'normal',
            'description' => 'Cambio de aceite',
            'started_at' => now()->subHours(5),
            'completed_at' => now()->subHours(1),
        ]);

        $response = $this->actingAs($client)
            ->getJson(route('client.orders.timeline', $order));

        $events = $response->json('timeline.events');

        $this->assertCount(3, $events);
        
        $eventTypes = array_column($events, 'type');
        $this->assertContains('order_created', $eventTypes);
        $this->assertContains('work_started', $eventTypes);
        $this->assertContains('work_completed', $eventTypes);
    }

    public function test_timeline_includes_maintenances_and_photos(): void
    {
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 42000,
            'status' => 'activo',
        ]);

        $order = ServiceOrder::create([
            'order_number' => 'OS-2026-0403',
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'created_by' => $client->id,
            'source' => 'manual',
            'status' => 'en_proceso',
            'progress' => 50,
            'priority' => 'normal',
            'description' => 'Revisión general',
        ]);

        $maintenance = Maintenance::create([
            'service_order_id' => $order->id,
            'vehicle_id' => $vehicle->id,
            'mechanic_id' => $client->id,
            'type' => 'preventivo',
            'description' => 'Cambio de aceite',
            'status' => 'completado',
            'performed_at' => now()->subHours(2),
            'mileage_at_service' => 42000,
        ]);

        $photo = ServicePhoto::create([
            'service_order_id' => $order->id,
            'user_id' => $client->id,
            'type' => 'evidence',
            'description' => 'Foto de evidencia',
            'photo_path' => 'storage/photos/evidence.jpg',
        ]);

        $response = $this->actingAs($client)
            ->getJson(route('client.orders.timeline', $order));

        $timeline = $response->json('timeline');

        $this->assertCount(1, $timeline['maintenances']);
        $this->assertEquals('Cambio de aceite', $timeline['maintenances'][0]['description']);

        $this->assertCount(1, $timeline['photos']);
        $this->assertEquals('evidence', $timeline['photos'][0]['type']);
    }
}
