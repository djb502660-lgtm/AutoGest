<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Vehicle;
use App\Policies\VehiclePolicy;
use PHPUnit\Framework\TestCase;

class VehiclePolicyTest extends TestCase
{
    private VehiclePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new VehiclePolicy;
    }

    private function user(UserRole $role, int $id): User
    {
        return (new User)->forceFill(['id' => $id, 'role' => $role]);
    }

    private function vehicle(int $clientId = 50): Vehicle
    {
        return (new Vehicle)->forceFill(['id' => 3, 'client_id' => $clientId]);
    }

    public function test_advisor_cannot_list_vehicles(): void
    {
        $this->assertTrue($this->policy->viewAny($this->user(UserRole::Admin, 1)));
        $this->assertTrue($this->policy->viewAny($this->user(UserRole::Mechanic, 1)));
        $this->assertTrue($this->policy->viewAny($this->user(UserRole::Client, 1)));
        $this->assertFalse($this->policy->viewAny($this->user(UserRole::Advisor, 1)));
    }

    public function test_admin_and_mechanic_can_view_any_vehicle(): void
    {
        $vehicle = $this->vehicle();

        $this->assertTrue($this->policy->view($this->user(UserRole::Admin, 1), $vehicle));
        $this->assertTrue($this->policy->view($this->user(UserRole::Mechanic, 2), $vehicle));
    }

    public function test_client_only_views_its_own_vehicle(): void
    {
        $this->assertTrue($this->policy->view($this->user(UserRole::Client, 50), $this->vehicle(50)));
        $this->assertFalse($this->policy->view($this->user(UserRole::Client, 51), $this->vehicle(50)));
        $this->assertFalse($this->policy->view($this->user(UserRole::Advisor, 50), $this->vehicle(50)));
    }

    public function test_only_admin_can_write_vehicles(): void
    {
        $admin = $this->user(UserRole::Admin, 1);
        $mechanic = $this->user(UserRole::Mechanic, 2);
        $vehicle = $this->vehicle();

        $this->assertTrue($this->policy->create($admin));
        $this->assertTrue($this->policy->update($admin, $vehicle));
        $this->assertTrue($this->policy->delete($admin, $vehicle));

        $this->assertFalse($this->policy->create($mechanic));
        $this->assertFalse($this->policy->update($mechanic, $vehicle));
        $this->assertFalse($this->policy->delete($mechanic, $vehicle));
    }
}
