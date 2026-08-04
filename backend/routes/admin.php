<?php

use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('vehicles', VehicleController::class)->except(['show']);
    Route::resource('maintenances', MaintenanceController::class);
    Route::get('/ordenes', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/ordenes/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::get('/ordenes/{order}/factura', [OrderController::class, 'invoice'])->name('admin.orders.invoice');
    Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reportes/generar', [ReportController::class, 'generate'])->name('reports.generate');
    Route::get('/reportes/pdf', [ReportController::class, 'downloadPdf'])->name('reports.pdf');
    Route::get('/reportes/csv', [ReportController::class, 'downloadCsv'])->name('reports.csv');
    Route::post('/reportes/enviar', [ReportController::class, 'sendEmail'])->name('reports.email');
    Route::get('/calendario', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendario/crear', [CalendarController::class, 'create'])->name('calendar.create');
    Route::post('/calendario', [CalendarController::class, 'store'])->name('calendar.store');
    Route::get('/calendario/{schedule}/editar', [CalendarController::class, 'edit'])->name('calendar.edit');
    Route::put('/calendario/{schedule}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendario/{schedule}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');

    // Inventory Management
    Route::get('/inventario', [App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
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
