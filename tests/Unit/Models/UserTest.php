<?php

namespace Tests\Unit\Models;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\ChatbotMessage;
use App\Models\Maintenance;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role, string $status = 'activo'): User
    {
        return (new User)->forceFill(['role' => $role, 'status' => $status]);
    }

    public function test_role_helpers_only_match_their_own_role(): void
    {
        $expectations = [
            [UserRole::Admin, 'isAdmin'],
            [UserRole::Advisor, 'isAdvisor'],
            [UserRole::Mechanic, 'isMechanic'],
            [UserRole::Client, 'isClient'],
        ];

        foreach ($expectations as [$role, $matching]) {
            $user = $this->user($role);

            foreach ($expectations as [, $helper]) {
                $this->assertSame($helper === $matching, $user->{$helper}(), "{$helper} for {$role->value}");
            }
        }
    }

    public function test_is_active_only_accepts_the_activo_status(): void
    {
        $this->assertTrue($this->user(UserRole::Client)->isActive());
        $this->assertFalse($this->user(UserRole::Client, 'inactivo')->isActive());
        $this->assertFalse($this->user(UserRole::Client, 'suspendido')->isActive());
    }

    public function test_role_is_cast_to_the_enum_and_secrets_are_hidden(): void
    {
        $user = (new User)->forceFill(['role' => 'mecanico']);

        $this->assertSame(UserRole::Mechanic, $user->role);
        $this->assertSame(UserRole::class, $user->getCasts()['role']);
        $this->assertSame('hashed', $user->getCasts()['password']);
        $this->assertSame(['password', 'remember_token'], $user->getHidden());
    }

    public function test_relations_use_the_expected_foreign_keys(): void
    {
        $user = new User;

        $this->assertInstanceOf(Vehicle::class, $user->vehicles()->getRelated());
        $this->assertSame('vehicles.client_id', $user->vehicles()->getQualifiedForeignKeyName());
        $this->assertSame('service_orders.client_id', $user->clientOrders()->getQualifiedForeignKeyName());
        $this->assertSame('service_orders.mechanic_id', $user->assignedOrders()->getQualifiedForeignKeyName());
        $this->assertSame('service_orders.advisor_id', $user->advisorOrders()->getQualifiedForeignKeyName());
        $this->assertSame('maintenances.mechanic_id', $user->maintenances()->getQualifiedForeignKeyName());
        $this->assertInstanceOf(ServiceOrder::class, $user->clientOrders()->getRelated());
        $this->assertInstanceOf(Maintenance::class, $user->maintenances()->getRelated());
        $this->assertInstanceOf(Alert::class, $user->alerts()->getRelated());
        $this->assertInstanceOf(ActivityLog::class, $user->activityLogs()->getRelated());
        $this->assertInstanceOf(ChatbotMessage::class, $user->chatbotMessages()->getRelated());
    }

    public function test_assigned_orders_query_eager_loads_vehicle_and_client(): void
    {
        $this->assertSame(
            ['vehicle', 'client'],
            array_keys((new User)->assignedOrdersQuery()->getEagerLoads()),
        );
    }

    public function test_accessible_vehicle_ids_merges_orders_and_maintenances_without_duplicates(): void
    {
        $mechanic = User::factory()->mechanic()->create();
        $client = User::factory()->client()->create();

        $vehicles = collect(['ACC-001', 'ACC-002', 'ACC-003'])->map(fn (string $plate) => Vehicle::create([
            'client_id' => $client->id,
            'plate' => $plate,
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2022,
            'mileage' => 1000,
            'status' => 'activo',
        ]));

        ServiceOrder::create([
            'order_number' => 'OS-ACC-1',
            'vehicle_id' => $vehicles[0]->id,
            'client_id' => $client->id,
            'mechanic_id' => $mechanic->id,
            'created_by' => $mechanic->id,
            'status' => 'en_proceso',
            'description' => 'Orden asignada',
        ]);

        Maintenance::create([
            'vehicle_id' => $vehicles[0]->id,
            'mechanic_id' => $mechanic->id,
            'type' => 'preventivo',
            'description' => 'Mismo vehículo que la orden',
            'status' => 'pendiente',
        ]);

        Maintenance::create([
            'vehicle_id' => $vehicles[1]->id,
            'mechanic_id' => $mechanic->id,
            'type' => 'correctivo',
            'description' => 'Solo mantenimiento',
            'status' => 'pendiente',
        ]);

        $accessible = $mechanic->accessibleVehicleIds();

        $this->assertEqualsCanonicalizing([$vehicles[0]->id, $vehicles[1]->id], $accessible->values()->all());
        $this->assertNotContains($vehicles[2]->id, $accessible);
    }

    public function test_accessible_vehicle_ids_is_empty_for_a_mechanic_without_work(): void
    {
        $this->assertTrue(User::factory()->mechanic()->create()->accessibleVehicleIds()->isEmpty());
    }
}
