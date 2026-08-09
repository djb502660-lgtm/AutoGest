<?php

namespace Tests\Unit\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetDemoPasswordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resets_the_password_and_reactivates_demo_accounts(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@autogest.test',
            'password' => 'otra-clave',
            'status' => 'inactivo',
        ]);

        $this->artisan('autogest:reset-demo-passwords')
            ->expectsOutputToContain('Contraseñas actualizadas: 1 cuenta(s).')
            ->assertExitCode(0);

        $admin->refresh();

        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertSame('activo', $admin->status);
    }

    public function test_it_warns_about_missing_demo_accounts(): void
    {
        $this->artisan('autogest:reset-demo-passwords')
            ->expectsOutputToContain('No existe: admin@autogest.test')
            ->expectsOutputToContain('Contraseñas actualizadas: 0 cuenta(s).')
            ->assertExitCode(0);
    }

    public function test_it_only_touches_demo_accounts(): void
    {
        $other = User::factory()->client()->create([
            'email' => 'real@example.com',
            'password' => 'clave-real',
            'status' => 'inactivo',
        ]);

        $this->artisan('autogest:reset-demo-passwords')->assertExitCode(0);

        $this->assertSame('inactivo', $other->refresh()->status);
        $this->assertFalse(Hash::check('password', $other->password));
    }

    public function test_it_updates_every_demo_account(): void
    {
        $emails = [
            'admin@autogest.test',
            'asesor1@autogest.test',
            'mecanico1@autogest.test',
            'mecanico2@autogest.test',
            'cliente1@autogest.test',
            'cliente2@autogest.test',
            'cliente3@autogest.test',
        ];

        foreach ($emails as $email) {
            User::factory()->create(['email' => $email, 'status' => 'inactivo']);
        }

        $this->artisan('autogest:reset-demo-passwords')
            ->expectsOutputToContain('Contraseñas actualizadas: 7 cuenta(s).')
            ->assertExitCode(0);

        $this->assertSame(0, User::where('status', 'inactivo')->count());
    }
}
