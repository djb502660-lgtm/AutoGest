<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * @param  string  ...$roles  Valores de UserRole: admin, asesor, mecanico, cliente
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
            if ($request->expectsJson()) {
                abort(403, 'No tienes permiso para acceder a este módulo.');
            }

            return $this->redirectToOwnPanel($request, $user);
        }

        return $next($request);
    }

    private function redirectToOwnPanel(Request $request, User $user): Response
    {
        $path = '/'.ltrim($request->path(), '/');

        if ($user->isAdmin() && preg_match('#^/asesor/ordenes/(\d+)$#', $path, $matches)) {
            return redirect()->route('admin.orders.show', $matches[1]);
        }

        if ($user->isAdmin() && preg_match('#^/asesor/solicitudes/(\d+)$#', $path, $matches)) {
            return redirect()->route('admin.chatbot-appointments.show', $matches[1]);
        }

        if ($user->isAdmin() && $path === '/asesor/solicitudes') {
            return redirect()->route('admin.chatbot-appointments.index');
        }

        return redirect()
            ->route($user->role->homeRouteName())
            ->with('error', 'No tienes permiso para acceder a ese módulo.');
    }
}
