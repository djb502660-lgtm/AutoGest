<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MechanicDashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect(match (auth()->user()->role) {
        \App\Enums\UserRole::Admin => route('dashboard'),
        \App\Enums\UserRole::Mechanic => route('mechanic.dashboard'),
        \App\Enums\UserRole::Client => route('client.dashboard'),
    });
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::middleware('role:mecanico')->group(function () {
        Route::get('/mecanico', [MechanicDashboardController::class, 'index'])->name('mechanic.dashboard');
    });

    Route::middleware('role:cliente')->group(function () {
        Route::get('/cliente', [ClientDashboardController::class, 'index'])->name('client.dashboard');
    });
});
