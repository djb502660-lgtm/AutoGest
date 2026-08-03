<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/advisor.php';
require __DIR__.'/mechanic.php';
require __DIR__.'/client.php';
require __DIR__.'/../../app/Modules/Chatbot/routes.php';
