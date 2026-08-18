<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Advisor\AppointmentRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/solicitudes-chatbot', [AppointmentRequestController::class, 'index'])->name('admin.chatbot-appointments.index');
    Route::get('/solicitudes-chatbot/{appointment}', [AppointmentRequestController::class, 'show'])->name('admin.chatbot-appointments.show');
    Route::post('/solicitudes-chatbot/{appointment}/confirmar', [AppointmentRequestController::class, 'confirm'])->name('admin.chatbot-appointments.confirm');
    Route::post('/solicitudes-chatbot/{appointment}/rechazar', [AppointmentRequestController::class, 'reject'])->name('admin.chatbot-appointments.reject');
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('vehicles', VehicleController::class)->except(['show']);
    Route::resource('maintenances', MaintenanceController::class);
    Route::get('/ordenes', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/ordenes/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    
    // Rutas de reportes
    Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reportes/vehiculo/{vehicleId}', [ReportController::class, 'vehicleDetail'])->name('reports.vehicle.detail');
    Route::get('/reportes/flota', [ReportController::class, 'vehicleFleet'])->name('reports.vehicle.fleet');
    Route::get('/reportes/vehiculo/{vehicleId}/pdf', [ReportController::class, 'downloadVehicleDetailPdf'])->name('reports.vehicle.detail.pdf');
    Route::get('/reportes/flota/pdf', [ReportController::class, 'downloadVehicleFleetPdf'])->name('reports.vehicle.fleet.pdf');
    Route::get('/reportes/generar', [ReportController::class, 'generate'])->name('reports.generate');
    Route::get('/reportes/csv', [ReportController::class, 'downloadCsv'])->name('reports.csv');
    Route::get('/reportes/pdf', [ReportController::class, 'downloadPdf'])->name('reports.pdf');
    // Ruta de prueba
    Route::get('/test-reportes', function() {
        return 'Ruta de reportes funciona correctamente';
    })->name('test.reportes');
    Route::get('/calendario', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendario/crear', [CalendarController::class, 'create'])->name('calendar.create');
    Route::post('/calendario', [CalendarController::class, 'store'])->name('calendar.store');
    Route::get('/calendario/{schedule}/editar', [CalendarController::class, 'edit'])->name('calendar.edit');
    Route::put('/calendario/{schedule}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendario/{schedule}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');

    // Audit Log
    Route::get('/auditoria', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/auditoria/{auditLog}', [AuditController::class, 'show'])->name('audit.show');

    // Inventory Management
    Route::get('/inventario', [InventoryController::class, 'index'])->name('inventory.index');
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('brands', BrandController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('purchases', PurchaseController::class)->except(['show']);
    Route::post('/purchases/{purchase}/recibir', [PurchaseController::class, 'receive'])->name('purchases.receive');
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/crear', [StockController::class, 'create'])->name('stock.create');
    Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
    Route::get('/stock/bajo', [StockController::class, 'lowStock'])->name('stock.low');
});
