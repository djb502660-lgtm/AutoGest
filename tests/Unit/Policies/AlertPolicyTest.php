<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\Alert;
use App\Models\User;
use App\Policies\AlertPolicy;
use PHPUnit\Framework\TestCase;

class AlertPolicyTest extends TestCase
{
    private AlertPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new AlertPolicy;
    }

    private function user(UserRole $role, int $id): User
    {
        return (new User)->forceFill(['id' => $id, 'role' => $role]);
    }

    private function alert(?int $userId): Alert
    {
        return (new Alert)->forceFill(['id' => 10, 'user_id' => $userId]);
    }

    public function test_any_authenticated_user_can_list_alerts(): void
    {
        $this->assertTrue($this->policy->viewAny($this->user(UserRole::Client, 1)));
    }

    public function test_admin_can_view_alerts_of_other_users(): void
    {
        $this->assertTrue($this->policy->view($this->user(UserRole::Admin, 1), $this->alert(2)));
    }

    public function test_non_admin_can_only_view_its_own_alerts(): void
    {
        $client = $this->user(UserRole::Client, 2);

        $this->assertTrue($this->policy->view($client, $this->alert(2)));
        $this->assertFalse($this->policy->view($client, $this->alert(3)));
        $this->assertFalse($this->policy->view($client, $this->alert(null)));
    }

    public function test_only_admin_can_create_alerts(): void
    {
        $this->assertTrue($this->policy->create($this->user(UserRole::Admin, 1)));
        $this->assertFalse($this->policy->create($this->user(UserRole::Advisor, 1)));
        $this->assertFalse($this->policy->create($this->user(UserRole::Mechanic, 1)));
    }

    public function test_owner_or_admin_can_update_an_alert(): void
    {
        $this->assertTrue($this->policy->update($this->user(UserRole::Admin, 1), $this->alert(2)));
        $this->assertTrue($this->policy->update($this->user(UserRole::Client, 2), $this->alert(2)));
        $this->assertFalse($this->policy->update($this->user(UserRole::Client, 5), $this->alert(2)));
    }

    public function test_only_admin_can_delete_or_resolve_an_alert(): void
    {
        $admin = $this->user(UserRole::Admin, 1);
        $owner = $this->user(UserRole::Client, 2);
        $alert = $this->alert(2);

        $this->assertTrue($this->policy->delete($admin, $alert));
        $this->assertTrue($this->policy->resolve($admin, $alert));
        $this->assertFalse($this->policy->delete($owner, $alert));
        $this->assertFalse($this->policy->resolve($owner, $alert));
    }
}
