<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect(match (auth()->user()->role) {
                UserRole::Admin => route('dashboard'),
                UserRole::Mechanic => route('mechanic.dashboard'),
                UserRole::Client => route('client.dashboard'),
            });
        }

        return view('home');
    }
}
