<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\ServiceOrderController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\ChatbotController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\PhotoController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

$registerMobileApi = function (): void {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::put('/user/profile', [AuthController::class, 'updateProfile']);
        Route::get('/dashboard', [DashboardController::class, 'show']);

        Route::get('/vehicles', [VehicleController::class, 'index']);
        Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);

        Route::get('/orders', [ServiceOrderController::class, 'index']);
        Route::get('/orders/{order}', [ServiceOrderController::class, 'show']);
        Route::post('/orders', [ServiceOrderController::class, 'store']);
        Route::put('/orders/{order}', [ServiceOrderController::class, 'update']);
        Route::put('/orders/{order}/status', [ServiceOrderController::class, 'updateStatus']);
        Route::post('/orders/{order}/comments', [CommentController::class, 'store']);
        Route::get('/orders/{order}/photos', [PhotoController::class, 'index']);
        Route::post('/orders/{order}/photos', [PhotoController::class, 'store']);
        Route::delete('/photos/{photo}', [PhotoController::class, 'destroy']);

        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm']);
        Route::post('/appointments/{appointment}/reject', [AppointmentController::class, 'reject']);

        Route::get('/chatbot', [ChatbotController::class, 'index']);
        Route::post('/chatbot/messages', [ChatbotController::class, 'message'])->middleware('throttle:60,1');

        Route::get('/expenses', [ExpenseController::class, 'index']);

        Route::get('/maintenances', [MaintenanceController::class, 'index']);
        Route::get('/maintenances/{maintenance}', [MaintenanceController::class, 'show']);
        Route::post('/maintenances', [MaintenanceController::class, 'store']);
        Route::put('/maintenances/{maintenance}', [MaintenanceController::class, 'update']);

        Route::middleware('role:admin')->group(function () {
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/{user}', [UserController::class, 'show']);
            Route::post('/users', [UserController::class, 'store']);
            Route::put('/users/{user}', [UserController::class, 'update']);
            Route::delete('/users/{user}', [UserController::class, 'destroy']);
        });
    });
};

$registerMobileApi();

Route::prefix('v1')->group($registerMobileApi);
