<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\ServiceOrderController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas públicas de autenticación
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas que requieren autenticación
Route::middleware('auth:sanctum')->group(function () {
    // Rutas de usuario
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    
    // Rutas de vehículos
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);
    
    // Rutas de órdenes de servicio
    Route::get('/orders', [ServiceOrderController::class, 'index']);
    Route::get('/orders/{order}', [ServiceOrderController::class, 'show']);
    Route::post('/orders', [ServiceOrderController::class, 'store']);
    Route::put('/orders/{order}', [ServiceOrderController::class, 'update']);
    Route::put('/orders/{order}/status', [ServiceOrderController::class, 'updateStatus']);
    
    // Rutas de mantenimientos
    Route::get('/maintenances', [MaintenanceController::class, 'index']);
    Route::get('/maintenances/{maintenance}', [MaintenanceController::class, 'show']);
    Route::post('/maintenances', [MaintenanceController::class, 'store']);
    Route::put('/maintenances/{maintenance}', [MaintenanceController::class, 'update']);
    
    // Rutas de usuarios (solo para administradores)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
});