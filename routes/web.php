<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/storage/{path}', function (string $path) {
    if (str_contains($path, '..') || str_contains($path, '\\')) {
        abort(404);
    }

    $fullPath = storage_path('app/public/'.$path);

    abort_unless(is_file($fullPath), 404);

    return response()->file($fullPath);
})->where('path', '.*')->name('storage.public');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/advisor.php';
require __DIR__.'/mechanic.php';
require __DIR__.'/client.php';
