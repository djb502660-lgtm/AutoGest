<?php

use App\Http\Controllers\Advisor\AppointmentController;
use App\Http\Controllers\Advisor\AppointmentRequestController;
use App\Http\Controllers\Advisor\ClientController;
use App\Http\Controllers\Advisor\DashboardController;
use App\Http\Controllers\Advisor\OrderController;
use App\Http\Controllers\Advisor\PreOrderController;
use App\Http\Controllers\Advisor\VehicleController;
use App\Http\Controllers\ServicePhotoController;
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

        // Pre-orders (manual and chatbot-generated)
        Route::resource('pre-orders', PreOrderController::class)->except(['show']);
        Route::get('/preordenes', [PreOrderController::class, 'index'])->name('pre-orders.index');
        Route::get('/preordenes/crear', [PreOrderController::class, 'create'])->name('pre-orders.create');
        Route::post('/preordenes', [PreOrderController::class, 'store'])->name('pre-orders.store');
        Route::get('/preordenes/{preOrder}', [PreOrderController::class, 'show'])->name('pre-orders.show');
        Route::get('/preordenes/{preOrder}/editar', [PreOrderController::class, 'edit'])->name('pre-orders.edit');
        Route::put('/preordenes/{preOrder}', [PreOrderController::class, 'update'])->name('pre-orders.update');
        Route::post('/preordenes/{preOrder}/confirmar', [PreOrderController::class, 'confirm'])->name('pre-orders.confirm');
        Route::post('/preordenes/{preOrder}/rechazar', [PreOrderController::class, 'reject'])->name('pre-orders.reject');
        Route::post('/preordenes/{preOrder}/convertir', [PreOrderController::class, 'convertToOrder'])->name('pre-orders.convert');
        Route::delete('/preordenes/{preOrder}', [PreOrderController::class, 'destroy'])->name('pre-orders.destroy');

        // Client Management
        Route::resource('clients', ClientController::class)->except(['show']);
        Route::get('/clientes', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clientes/crear', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clientes', [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clientes/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::get('/clientes/{client}/editar', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clientes/{client}', [ClientController::class, 'update'])->name('clients.update');

        // Vehicle Management
        Route::resource('vehicles', VehicleController::class)->except(['show']);
        Route::get('/vehiculos', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/vehiculos/crear', [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('/vehiculos', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('/vehiculos/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
        Route::get('/vehiculos/{vehicle}/editar', [VehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('/vehiculos/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');

        // Appointments (Agenda)
        Route::resource('appointments', AppointmentController::class)->except(['show']);
        Route::get('/citas', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/citas/crear', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/citas', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::get('/citas/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
        Route::get('/citas/{appointment}/editar', [AppointmentController::class, 'edit'])->name('appointments.edit');
        Route::put('/citas/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
        Route::post('/citas/{appointment}/reprogramar', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
        Route::get('/citas/{appointment}/cancelar', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
        Route::get('/citas/calendario', [AppointmentController::class, 'calendar'])->name('appointments.calendar');

        // Service Photos
        Route::get('/ordenes/{order}/fotos', [ServicePhotoController::class, 'index'])->name('orders.photos.index');
        Route::get('/ordenes/{order}/fotos/antes-despues', [ServicePhotoController::class, 'beforeAfter'])->name('orders.photos.before-after');
        Route::post('/ordenes/{order}/fotos', [ServicePhotoController::class, 'store'])->name('orders.photos.store');
        Route::delete('/fotos/{photo}', [ServicePhotoController::class, 'destroy'])->name('photos.destroy');
    });
