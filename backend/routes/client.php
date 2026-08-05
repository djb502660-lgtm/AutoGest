<?php

use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\ExpenseController;
use App\Http\Controllers\Client\MaintenanceController;
use App\Http\Controllers\Client\NotificationController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\ServiceTimelineController;
use App\Http\Controllers\Client\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:cliente'])
    ->prefix('cliente')
    ->name('client.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/vehiculos', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/vehiculos/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
        Route::get('/mantenimientos/historial', [MaintenanceController::class, 'history'])->name('maintenances.history');
        Route::get('/mantenimientos/proximos', [MaintenanceController::class, 'upcoming'])->name('maintenances.upcoming');
        Route::get('/ordenes', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/ordenes/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/ordenes/{order}/timeline', [ServiceTimelineController::class, 'show'])->name('orders.timeline');
        Route::get('/gastos', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notifications.index');
        Route::put('/notificaciones/{alert}/leer', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
        // Chatbot routes moved to module: app/Modules/Chatbot/routes.php
    });
