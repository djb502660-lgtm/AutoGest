<?php

namespace Tests\Unit\Models;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_stores_the_action_for_the_given_user(): void
    {
        $user = User::factory()->admin()->create();

        $log = ActivityLog::record('orden.creada', 'Creó una orden', user: $user);

        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('orden.creada', $log->action);
        $this->assertSame('Creó una orden', $log->description);
        $this->assertNull($log->model_type);
        $this->assertNull($log->model_id);
    }

    public function test_record_stores_the_related_model_reference(): void
    {
        $client = User::factory()->client()->create();
        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'LOG-001',
            'brand' => 'Kia',
            'model' => 'Rio',
            'year' => 2019,
            'mileage' => 30000,
            'status' => 'activo',
        ]);

        $log = ActivityLog::record('vehiculo.actualizado', model: $vehicle, user: $client);

        $this->assertSame(Vehicle::class, $log->model_type);
        $this->assertSame($vehicle->id, $log->model_id);
        $this->assertNull($log->description);
    }

    public function test_record_falls_back_to_the_authenticated_user(): void
    {
        $user = User::factory()->advisor()->create();
        $this->actingAs($user);

        $this->assertSame($user->id, ActivityLog::record('login')->user_id);
    }

    public function test_record_allows_a_guest_action(): void
    {
        $this->assertNull(ActivityLog::record('login.fallido')->user_id);
    }

    public function test_log_belongs_to_a_user(): void
    {
        $this->assertInstanceOf(User::class, (new ActivityLog)->user()->getRelated());
    }
}
