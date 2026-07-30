<?php

use App\Modules\Chatbot\Http\Controllers\Client\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'role:cliente'])
    ->prefix('cliente')
    ->name('client.')
    ->group(function () {
        Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');
        Route::post('/chatbot/mensaje', [ChatbotController::class, 'message'])
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
            ->name('chatbot.message');
    });
