<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClientChatbotController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\ClientExpenseController;
use App\Http\Controllers\ClientMaintenanceController;
use App\Http\Controllers\ClientNotificationController;
use App\Http\Controllers\ClientOrderController;
use App\Http\Controllers\ClientProfileController;
use App\Http\Controllers\ClientVehicleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MechanicDashboardController;
use App\Http\Controllers\MechanicMaintenanceController;
use App\Http\Controllers\MechanicOrderController;
use App\Http\Controllers\MechanicVehicleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
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
        Route::resource('vehicles', VehicleController::class)->except(['show']);
        Route::resource('maintenances', MaintenanceController::class)->except(['show']);
        Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reportes/generar', [ReportController::class, 'generate'])->name('reports.generate');
        Route::get('/calendario', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendario/crear', [CalendarController::class, 'create'])->name('calendar.create');
        Route::post('/calendario', [CalendarController::class, 'store'])->name('calendar.store');
        Route::get('/calendario/{schedule}/editar', [CalendarController::class, 'edit'])->name('calendar.edit');
        Route::put('/calendario/{schedule}', [CalendarController::class, 'update'])->name('calendar.update');
        Route::delete('/calendario/{schedule}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    });

    Route::middleware('role:mecanico')->prefix('mecanico')->name('mechanic.')->group(function () {
        Route::get('/', [MechanicDashboardController::class, 'index'])->name('dashboard');
        Route::get('/ordenes', [MechanicOrderController::class, 'index'])->name('orders.index');
        Route::get('/ordenes/{order}', [MechanicOrderController::class, 'show'])->name('orders.show');
        Route::put('/ordenes/{order}/estado', [MechanicOrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/ordenes/{order}/comentarios', [MechanicOrderController::class, 'storeComment'])->name('orders.comments');
        Route::put('/ordenes/{order}/avance', [MechanicOrderController::class, 'updateProgress'])->name('orders.progress');
        Route::get('/mantenimientos/crear', [MechanicMaintenanceController::class, 'create'])->name('maintenances.create');
        Route::post('/mantenimientos', [MechanicMaintenanceController::class, 'store'])->name('maintenances.store');
        Route::get('/vehiculos', [MechanicVehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/vehiculos/{vehicle}', [MechanicVehicleController::class, 'show'])->name('vehicles.show');
        Route::get('/historial', [MechanicOrderController::class, 'history'])->name('history');
    });

    Route::middleware('role:cliente')->prefix('cliente')->name('client.')->group(function () {
        Route::get('/', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('/vehiculos', [ClientVehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/vehiculos/{vehicle}', [ClientVehicleController::class, 'show'])->name('vehicles.show');
        Route::get('/mantenimientos/historial', [ClientMaintenanceController::class, 'history'])->name('maintenances.history');
        Route::get('/mantenimientos/proximos', [ClientMaintenanceController::class, 'upcoming'])->name('maintenances.upcoming');
        Route::get('/ordenes', [ClientOrderController::class, 'index'])->name('orders.index');
        Route::get('/ordenes/{order}', [ClientOrderController::class, 'show'])->name('orders.show');
        Route::get('/gastos', [ClientExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/notificaciones', [ClientNotificationController::class, 'index'])->name('notifications.index');
        Route::put('/notificaciones/{alert}/leer', [ClientNotificationController::class, 'markRead'])->name('notifications.read');
        Route::get('/perfil', [ClientProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/perfil', [ClientProfileController::class, 'update'])->name('profile.update');
        Route::get('/chatbot', [ClientChatbotController::class, 'index'])->name('chatbot.index');
        Route::post('/chatbot/mensaje', [ClientChatbotController::class, 'message'])->name('chatbot.message');
    });
});
