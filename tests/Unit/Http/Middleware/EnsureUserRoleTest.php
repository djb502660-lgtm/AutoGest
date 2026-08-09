<?php

namespace Tests\Unit\Http\Middleware;

use App\Enums\UserRole;
use App\Http\Middleware\EnsureUserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureUserRoleTest extends TestCase
{
    private function request(?User $user): Request
    {
        $request = Request::create('/admin');
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    private function handle(?User $user, string ...$roles): Response
    {
        return (new EnsureUserRole)->handle(
            $this->request($user),
            fn () => new Response('ok'),
            ...$roles,
        );
    }

    private function user(UserRole $role, string $status = 'activo'): User
    {
        return (new User)->forceFill(['id' => 1, 'role' => $role, 'status' => $status]);
    }

    public function test_it_passes_a_user_with_an_allowed_role(): void
    {
        $response = $this->handle($this->user(UserRole::Admin), 'admin');

        $this->assertSame('ok', $response->getContent());
    }

    public function test_it_accepts_any_of_the_listed_roles(): void
    {
        $this->assertSame('ok', $this->handle($this->user(UserRole::Mechanic), 'admin', 'mecanico')->getContent());
    }

    public function test_it_rejects_a_guest(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Acceso no autorizado.');

        $this->handle(null, 'admin');
    }

    public function test_it_rejects_an_inactive_user(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Acceso no autorizado.');

        $this->handle($this->user(UserRole::Admin, 'inactivo'), 'admin');
    }

    public function test_it_rejects_a_role_that_is_not_listed(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('No tienes permiso para acceder a este módulo.');

        $this->handle($this->user(UserRole::Client), 'admin', 'asesor');
    }

    public function test_it_forbids_access_when_no_role_is_configured(): void
    {
        $this->expectException(HttpException::class);

        $this->handle($this->user(UserRole::Admin));
    }

    public function test_it_fails_loudly_on_an_unknown_role_name(): void
    {
        $this->expectException(\ValueError::class);

        $this->handle($this->user(UserRole::Admin), 'gerente');
    }

    public function test_rejections_use_the_forbidden_status_code(): void
    {
        try {
            $this->handle($this->user(UserRole::Client), 'admin');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());

            return;
        }

        $this->fail('Se esperaba una excepción 403.');
    }
}
