<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\VehicleModelTemplate;
use App\Policies\VehicleModelTemplatePolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VehicleModelTemplatePolicyTest extends TestCase
{
    private VehicleModelTemplatePolicy $policy;

    private VehicleModelTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new VehicleModelTemplatePolicy;
        $this->template = (new VehicleModelTemplate)->forceFill(['id' => 1]);
    }

    private function user(UserRole $role): User
    {
        return (new User)->forceFill(['id' => 1, 'role' => $role]);
    }

    public function test_admin_is_allowed_on_every_ability(): void
    {
        $admin = $this->user(UserRole::Admin);

        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertTrue($this->policy->view($admin, $this->template));
        $this->assertTrue($this->policy->create($admin));
        $this->assertTrue($this->policy->update($admin, $this->template));
        $this->assertTrue($this->policy->delete($admin, $this->template));
    }

    /**
     * @return array<string, array{UserRole}>
     */
    public static function nonAdminRoles(): array
    {
        return [
            'advisor' => [UserRole::Advisor],
            'mechanic' => [UserRole::Mechanic],
            'client' => [UserRole::Client],
        ];
    }

    #[DataProvider('nonAdminRoles')]
    public function test_non_admin_roles_are_denied_on_every_ability(UserRole $role): void
    {
        $user = $this->user($role);

        $this->assertFalse($this->policy->viewAny($user));
        $this->assertFalse($this->policy->view($user, $this->template));
        $this->assertFalse($this->policy->create($user));
        $this->assertFalse($this->policy->update($user, $this->template));
        $this->assertFalse($this->policy->delete($user, $this->template));
    }
}
