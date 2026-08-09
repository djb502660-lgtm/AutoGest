<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\UserPolicy;
use PHPUnit\Framework\TestCase;

class UserPolicyTest extends TestCase
{
    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserPolicy;
    }

    private function user(UserRole $role, int $id): User
    {
        return (new User)->forceFill(['id' => $id, 'role' => $role]);
    }

    public function test_only_admin_can_list_or_create_users(): void
    {
        $admin = $this->user(UserRole::Admin, 1);
        $advisor = $this->user(UserRole::Advisor, 2);

        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertTrue($this->policy->create($admin));
        $this->assertFalse($this->policy->viewAny($advisor));
        $this->assertFalse($this->policy->create($advisor));
    }

    public function test_user_can_view_and_update_its_own_profile(): void
    {
        $client = $this->user(UserRole::Client, 5);
        $other = $this->user(UserRole::Client, 6);

        $this->assertTrue($this->policy->view($client, $client));
        $this->assertTrue($this->policy->update($client, $client));
        $this->assertFalse($this->policy->view($client, $other));
        $this->assertFalse($this->policy->update($client, $other));
    }

    public function test_admin_can_view_and_update_other_users(): void
    {
        $admin = $this->user(UserRole::Admin, 1);
        $client = $this->user(UserRole::Client, 5);

        $this->assertTrue($this->policy->view($admin, $client));
        $this->assertTrue($this->policy->update($admin, $client));
    }

    public function test_admin_cannot_delete_itself(): void
    {
        $admin = $this->user(UserRole::Admin, 1);

        $this->assertTrue($this->policy->delete($admin, $this->user(UserRole::Client, 5)));
        $this->assertFalse($this->policy->delete($admin, $admin));
    }

    public function test_non_admin_cannot_delete_or_assign_roles(): void
    {
        $advisor = $this->user(UserRole::Advisor, 2);
        $client = $this->user(UserRole::Client, 5);

        $this->assertFalse($this->policy->delete($advisor, $client));
        $this->assertFalse($this->policy->assignRole($advisor, $client));
        $this->assertTrue($this->policy->assignRole($this->user(UserRole::Admin, 1), $client));
    }
}
