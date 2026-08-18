<?php

use App\Enums\UserRole;
use App\Http\Middleware\EnsureUserRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserRole::class,
        ]);

        $middleware->trustProxies(at: '*');

        $middleware->redirectUsersTo(function (Request $request): string {
            $role = $request->user()?->role;

            return $role instanceof UserRole
                ? route($role->homeRouteName())
                : '/';
        });
        
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->is('login') && $request->isMethod('POST')) {
                return redirect()
                    ->route('login')
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => 'La sesión expiró o la página estaba desactualizada. Vuelve a intentarlo.',
                    ]);
            }

            return null;
        });
    })
    ->create();
