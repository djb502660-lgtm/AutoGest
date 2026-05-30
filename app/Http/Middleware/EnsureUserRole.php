<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * @param  string  ...$roles  Valores de UserRole: admin, mecanico, cliente
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isActive()) {
            abort(403, 'Acceso no autorizado.');
        }

        $allowed = collect($roles)->map(
            fn (string $role) => UserRole::from($role),
        );

        if (! $allowed->contains($user->role)) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
