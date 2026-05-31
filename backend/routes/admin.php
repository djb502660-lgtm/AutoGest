<?php

use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('vehicles', VehicleController::class)->except(['show']);
    Route::resource('maintenances', MaintenanceController::class)->except(['show']);
    Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reportes/generar', [ReportController::class, 'generate'])->name('reports.generate');
    Route::get('/reportes/pdf', [ReportController::class, 'downloadPdf'])->name('reports.pdf');
    Route::post('/reportes/enviar', [ReportController::class, 'sendEmail'])->name('reports.email');
    Route::get('/calendario', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendario/crear', [CalendarController::class, 'create'])->name('calendar.create');
    Route::post('/calendario', [CalendarController::class, 'store'])->name('calendar.store');
    Route::get('/calendario/{schedule}/editar', [CalendarController::class, 'edit'])->name('calendar.edit');
    Route::put('/calendario/{schedule}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendario/{schedule}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
});
