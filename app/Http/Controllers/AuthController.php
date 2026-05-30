<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

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

            return redirect()->intended(match ($user->role) {
                UserRole::Admin => route('dashboard'),
                UserRole::Mechanic => route('mechanic.dashboard'),
                UserRole::Client => route('client.dashboard'),
            });
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
}
