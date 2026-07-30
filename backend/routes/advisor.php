<?php

use App\Http\Controllers\Advisor\AppointmentRequestController;
use App\Http\Controllers\Advisor\DashboardController;
use App\Http\Controllers\Advisor\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:asesor'])
    ->prefix('asesor')
    ->name('advisor.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/ordenes', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/ordenes/crear', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/ordenes', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/ordenes/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/ordenes/{order}/editar', [OrderController::class, 'edit'])->name('orders.edit');
        Route::put('/ordenes/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::put('/ordenes/{order}/asignar-mecanico', [OrderController::class, 'assignMechanic'])->name('orders.assign');

        Route::get('/solicitudes', [AppointmentRequestController::class, 'index'])->name('appointments.index');
        Route::get('/solicitudes/{appointment}', [AppointmentRequestController::class, 'show'])->name('appointments.show');
        Route::post('/solicitudes/{appointment}/confirmar', [AppointmentRequestController::class, 'confirm'])->name('appointments.confirm');
        Route::post('/solicitudes/{appointment}/rechazar', [AppointmentRequestController::class, 'reject'])->name('appointments.reject');
        Route::get('/vehiculos/{vehicle}/plantillas', [AppointmentRequestController::class, 'vehicleTemplates'])->name('vehicles.templates');
    });
