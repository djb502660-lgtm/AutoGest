<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\Maintenance;
use App\Models\User;
use App\Models\Vehicle;
use App\Policies\MaintenancePolicy;
use Tests\TestCase;

class MaintenancePolicyTest extends TestCase
{
    private MaintenancePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new MaintenancePolicy;
    }

    private function user(UserRole $role, int $id): User
    {
        return (new User)->forceFill(['id' => $id, 'role' => $role]);
    }

    private function maintenance(?int $mechanicId, ?int $vehicleClientId = null): Maintenance
    {
        $maintenance = (new Maintenance)->forceFill(['id' => 7, 'mechanic_id' => $mechanicId]);

        if ($vehicleClientId !== null) {
            $maintenance->setRelation('vehicle', (new Vehicle)->forceFill([
                'id' => 3,
                'client_id' => $vehicleClientId,
            ]));
        }

        return $maintenance;
    }

    public function test_advisor_cannot_list_maintenances(): void
    {
        $this->assertTrue($this->policy->viewAny($this->user(UserRole::Admin, 1)));
        $this->assertTrue($this->policy->viewAny($this->user(UserRole::Mechanic, 1)));
        $this->assertTrue($this->policy->viewAny($this->user(UserRole::Client, 1)));
        $this->assertFalse($this->policy->viewAny($this->user(UserRole::Advisor, 1)));
    }

    public function test_admin_can_view_any_maintenance(): void
    {
        $this->assertTrue($this->policy->view($this->user(UserRole::Admin, 1), $this->maintenance(9)));
    }

    public function test_mechanic_only_views_maintenances_assigned_to_itself(): void
    {
        $mechanic = $this->user(UserRole::Mechanic, 4);

        $this->assertTrue($this->policy->view($mechanic, $this->maintenance(4)));
        $this->assertFalse($this->policy->view($mechanic, $this->maintenance(5)));
    }

    public function test_client_only_views_maintenances_of_its_own_vehicles(): void
    {
        $client = $this->user(UserRole::Client, 8);

        $this->assertTrue($this->policy->view($client, $this->maintenance(4, vehicleClientId: 8)));
        $this->assertFalse($this->policy->view($client, $this->maintenance(4, vehicleClientId: 9)));
    }

    public function test_client_cannot_view_a_maintenance_without_vehicle(): void
    {
        $this->assertFalse($this->policy->view($this->user(UserRole::Client, 8), $this->maintenance(4)));
    }

    public function test_advisor_cannot_view_a_maintenance(): void
    {
        $this->assertFalse($this->policy->view($this->user(UserRole::Advisor, 8), $this->maintenance(4, vehicleClientId: 8)));
    }

    public function test_only_admin_and_mechanic_can_create_maintenances(): void
    {
        $this->assertTrue($this->policy->create($this->user(UserRole::Admin, 1)));
        $this->assertTrue($this->policy->create($this->user(UserRole::Mechanic, 1)));
        $this->assertFalse($this->policy->create($this->user(UserRole::Advisor, 1)));
        $this->assertFalse($this->policy->create($this->user(UserRole::Client, 1)));
    }

    public function test_mechanic_updates_only_its_own_maintenance(): void
    {
        $this->assertTrue($this->policy->update($this->user(UserRole::Admin, 1), $this->maintenance(4)));
        $this->assertTrue($this->policy->update($this->user(UserRole::Mechanic, 4), $this->maintenance(4)));
        $this->assertFalse($this->policy->update($this->user(UserRole::Mechanic, 5), $this->maintenance(4)));
        $this->assertFalse($this->policy->update($this->user(UserRole::Client, 4), $this->maintenance(4)));
    }

    public function test_only_admin_can_delete_a_maintenance(): void
    {
        $this->assertTrue($this->policy->delete($this->user(UserRole::Admin, 1), $this->maintenance(4)));
        $this->assertFalse($this->policy->delete($this->user(UserRole::Mechanic, 4), $this->maintenance(4)));
    }
}
