<?php

namespace Tests\Feature;

use App\Models\AppointmentRequest;
use App\Models\ServiceOrder;
use App\Models\ServicePhoto;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiV1MobileTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_login_and_load_v1_dashboard(): void
    {
        $client = User::factory()->client()->create([
            'email' => 'mobile-client@autogest.test',
            'password' => 'password',
        ]);

        $this->seedClientFleet($client);

        $login = $this->postJson('/api/v1/login', [
            'email' => 'mobile-client@autogest.test',
            'password' => 'password',
        ]);

        $login->assertOk()->assertJsonPath('user.role', 'cliente');
        $token = $login->json('token');

        $this->withToken($token)
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('role', 'cliente')
            ->assertJsonPath('stats.vehicles', 1);

        $this->withToken($token)
            ->getJson('/api/v1/vehicles')
            ->assertOk()
            ->assertJsonCount(1, 'vehicles');

        $this->withToken($token)
            ->getJson('/api/v1/orders')
            ->assertOk()
            ->assertJsonCount(1, 'orders');
    }

    public function test_advisor_can_list_and_confirm_chatbot_appointments(): void
    {
        $client = User::factory()->client()->create();
        $advisor = User::factory()->advisor()->create(['password' => 'password']);
        $mechanic = User::factory()->mechanic()->create();
        $vehicle = $this->makeVehicle($client);

        $appointment = AppointmentRequest::create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '09:00:00',
            'service_type' => 'Revision general',
            'description' => 'Cita chatbot',
            'status' => 'pendiente',
            'source' => 'chatbot',
        ]);

        $token = $advisor->createToken('mobile-app')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/appointments?status=pendiente')
            ->assertOk()
            ->assertJsonCount(1, 'appointments');

        $this->withToken($token)
            ->postJson('/api/v1/appointments/'.$appointment->id.'/confirm', [
                'mechanic_id' => $mechanic->id,
            ])
            ->assertOk()
            ->assertJsonPath('appointment.status', 'convertida');
    }

    public function test_mechanic_can_update_status_and_upload_photo_with_https_url(): void
    {
        Storage::fake('public');

        $client = User::factory()->client()->create();
        $mechanic = User::factory()->mechanic()->create();
        $vehicle = $this->makeVehicle($client);
        $order = $this->makeOrder($client, $vehicle, $mechanic);

        $token = $mechanic->createToken('mobile-app')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/v1/orders/'.$order->id.'/status', [
                'status' => 'en_proceso',
                'progress' => 40,
            ])
            ->assertOk()
            ->assertJsonPath('order.status', 'en_proceso');

        $upload = $this->withToken($token)
            ->post('/api/v1/orders/'.$order->id.'/photos', [
                'photo' => UploadedFile::fake()->image('reception.jpg'),
                'type' => 'reception',
                'description' => 'Ingreso',
            ], ['Accept' => 'application/json']);

        $upload->assertCreated();
        $this->assertStringContainsString('/storage/', (string) $upload->json('photo.url'));
    }

    public function test_photo_model_builds_absolute_url(): void
    {
        $photo = new ServicePhoto(['photo_path' => 'service-photos/mobile.jpg']);

        $this->assertSame(url('storage/service-photos/mobile.jpg'), $photo->url);
    }

    public function test_client_can_send_chatbot_message(): void
    {
        $client = User::factory()->client()->create();
        $token = $client->createToken('mobile-app')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/chatbot/messages', ['message' => 'hola'])
            ->assertOk()
            ->assertJsonStructure(['reply']);
    }

    public function test_admin_can_load_operations_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->client()->create();
        $mechanic = User::factory()->mechanic()->create();
        $vehicle = $this->makeVehicle($client);
        $this->makeOrder($client, $vehicle, $mechanic);

        $token = $admin->createToken('mobile-app')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('role', 'admin')
            ->assertJsonPath('stats.open_orders', 1)
            ->assertJsonStructure([
                'stats' => ['open_orders', 'pending_appointments', 'vehicles', 'users'],
                'recent_orders',
                'pending_appointments',
            ]);
    }

    private function seedClientFleet(User $client): void
    {
        $vehicle = $this->makeVehicle($client);
        $this->makeOrder($client, $vehicle, User::factory()->mechanic()->create());
    }

    private function makeVehicle(User $client): Vehicle
    {
        return Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'MOV-'.fake()->unique()->numerify('###'),
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2021,
            'mileage' => 42000,
            'status' => 'activo',
        ]);
    }

    private function makeOrder(User $client, Vehicle $vehicle, User $mechanic): ServiceOrder
    {
        return ServiceOrder::create([
            'order_number' => 'OS-MOV-'.fake()->unique()->numerify('####'),
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'mechanic_id' => $mechanic->id,
            'created_by' => $client->id,
            'source' => 'manual',
            'status' => 'recibida',
            'progress' => 0,
            'priority' => 'normal',
            'description' => 'Revision motor',
        ]);
    }
}
