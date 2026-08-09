<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Policies\ServiceOrderPolicy;
use PHPUnit\Framework\TestCase;

class ServiceOrderPolicyTest extends TestCase
{
    private ServiceOrderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ServiceOrderPolicy;
    }

    private function user(UserRole $role, int $id): User
    {
        return (new User)->forceFill(['id' => $id, 'role' => $role]);
    }

    private function order(array $attributes = []): ServiceOrder
    {
        return (new ServiceOrder)->forceFill($attributes + [
            'id' => 20,
            'client_id' => 100,
            'mechanic_id' => 200,
            'advisor_id' => 300,
            'created_by' => 300,
        ]);
    }

    public function test_all_roles_can_list_orders(): void
    {
        foreach (UserRole::cases() as $role) {
            $this->assertTrue($this->policy->viewAny($this->user($role, 1)));
        }
    }

    public function test_admin_can_view_any_order(): void
    {
        $this->assertTrue($this->policy->view($this->user(UserRole::Admin, 1), $this->order()));
    }

    public function test_advisor_views_orders_it_owns_or_created(): void
    {
        $this->assertTrue($this->policy->view($this->user(UserRole::Advisor, 300), $this->order()));
        $this->assertTrue($this->policy->view(
            $this->user(UserRole::Advisor, 301),
            $this->order(['advisor_id' => 999, 'created_by' => 301]),
        ));
        $this->assertFalse($this->policy->view($this->user(UserRole::Advisor, 302), $this->order()));
    }

    public function test_mechanic_views_only_orders_assigned_to_itself(): void
    {
        $this->assertTrue($this->policy->view($this->user(UserRole::Mechanic, 200), $this->order()));
        $this->assertFalse($this->policy->view($this->user(UserRole::Mechanic, 201), $this->order()));
    }

    public function test_client_views_only_its_own_orders(): void
    {
        $this->assertTrue($this->policy->view($this->user(UserRole::Client, 100), $this->order()));
        $this->assertFalse($this->policy->view($this->user(UserRole::Client, 101), $this->order()));
    }

    public function test_only_admin_and_advisor_can_create_orders(): void
    {
        $this->assertTrue($this->policy->create($this->user(UserRole::Admin, 1)));
        $this->assertTrue($this->policy->create($this->user(UserRole::Advisor, 1)));
        $this->assertFalse($this->policy->create($this->user(UserRole::Mechanic, 1)));
        $this->assertFalse($this->policy->create($this->user(UserRole::Client, 1)));
    }

    public function test_update_follows_the_same_ownership_rules_as_view(): void
    {
        $this->assertTrue($this->policy->update($this->user(UserRole::Admin, 1), $this->order()));
        $this->assertTrue($this->policy->update($this->user(UserRole::Advisor, 300), $this->order()));
        $this->assertFalse($this->policy->update($this->user(UserRole::Advisor, 302), $this->order()));
        $this->assertTrue($this->policy->update($this->user(UserRole::Mechanic, 200), $this->order()));
        $this->assertFalse($this->policy->update($this->user(UserRole::Mechanic, 201), $this->order()));
    }

    public function test_client_cannot_update_its_own_order(): void
    {
        $this->assertFalse($this->policy->update($this->user(UserRole::Client, 100), $this->order()));
    }

    public function test_only_admin_can_delete_an_order(): void
    {
        $this->assertTrue($this->policy->delete($this->user(UserRole::Admin, 1), $this->order()));
        $this->assertFalse($this->policy->delete($this->user(UserRole::Advisor, 300), $this->order()));
    }

    public function test_only_admin_and_advisor_can_assign_an_order(): void
    {
        $order = $this->order();

        $this->assertTrue($this->policy->assign($this->user(UserRole::Admin, 1), $order));
        $this->assertTrue($this->policy->assign($this->user(UserRole::Advisor, 302), $order));
        $this->assertFalse($this->policy->assign($this->user(UserRole::Mechanic, 200), $order));
        $this->assertFalse($this->policy->assign($this->user(UserRole::Client, 100), $order));
    }
}
