<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = [
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = $request->user();

            if (! $user->isActive()) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Tu cuenta está inactiva. Contacta al administrador.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);

            ActivityLog::record('login', 'Inicio de sesión en el sistema.', user: $user);

            return redirect()->to($this->homeUrl($user->role, $request));
        }

        return back()->withErrors([
            'email' => 'Las credenciales ingresadas no son válidas.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            ActivityLog::record('logout', 'Cierre de sesión.', user: $request->user());
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function homeUrl(UserRole $role, Request $request): string
    {
        $home = route($role->homeRouteName());
        $intended = $request->session()->pull('url.intended');

        if (! is_string($intended) || $intended === '') {
            return $home;
        }

        $path = parse_url($intended, PHP_URL_PATH) ?: '';

        return $role->allowsPath($path) ? $intended : $home;
    }
}
