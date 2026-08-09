<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_backed_values_match_the_database_representation(): void
    {
        $this->assertSame('admin', UserRole::Admin->value);
        $this->assertSame('asesor', UserRole::Advisor->value);
        $this->assertSame('mecanico', UserRole::Mechanic->value);
        $this->assertSame('cliente', UserRole::Client->value);
    }

    public function test_label_returns_a_human_readable_name_for_every_case(): void
    {
        $this->assertSame('Administrador', UserRole::Admin->label());
        $this->assertSame('Asesor de servicio', UserRole::Advisor->label());
        $this->assertSame('Mecánico', UserRole::Mechanic->label());
        $this->assertSame('Cliente', UserRole::Client->label());
    }

    public function test_every_case_has_a_label(): void
    {
        foreach (UserRole::cases() as $role) {
            $this->assertNotSame('', $role->label());
        }
    }

    public function test_from_resolves_stored_values(): void
    {
        $this->assertSame(UserRole::Mechanic, UserRole::from('mecanico'));
        $this->assertNull(UserRole::tryFrom('gerente'));
    }
}
