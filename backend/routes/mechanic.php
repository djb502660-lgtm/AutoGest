<?php

use App\Http\Controllers\Mechanic\CalendarController;
use App\Http\Controllers\Mechanic\DashboardController;
use App\Http\Controllers\Mechanic\MaintenanceController;
use App\Http\Controllers\Mechanic\OrderController;
use App\Http\Controllers\Mechanic\VehicleController;
use App\Http\Controllers\ServicePhotoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:mecanico'])
    ->prefix('mecanico')
    ->name('mechanic.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/ordenes', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/ordenes/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::put('/ordenes/{order}/estado', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/ordenes/{order}/comentarios', [OrderController::class, 'storeComment'])->name('orders.comments');
        Route::put('/ordenes/{order}/avance', [OrderController::class, 'updateProgress'])->name('orders.progress');
        Route::get('/vehiculos', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/vehiculos/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
        Route::get('/historial', [OrderController::class, 'history'])->name('history');
        Route::get('/calendario', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/mantenimientos/crear', [MaintenanceController::class, 'create'])->name('maintenances.create');
        Route::post('/mantenimientos', [MaintenanceController::class, 'store'])->name('maintenances.store');

        // Service Photos (Mecánico - Obligatorio para evidencias)
        Route::get('/ordenes/{order}/fotos', [ServicePhotoController::class, 'index'])->name('orders.photos.index');
        Route::get('/ordenes/{order}/fotos/antes-despues', [ServicePhotoController::class, 'beforeAfter'])->name('orders.photos.before-after');
        Route::post('/ordenes/{order}/fotos', [ServicePhotoController::class, 'store'])->name('orders.photos.store');
        Route::delete('/fotos/{photo}', [ServicePhotoController::class, 'destroy'])->name('photos.destroy');
    });
