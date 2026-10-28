<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
    Route::get('/products/{slug}', [ProductController::class, 'show'])
        ->where('slug', '[a-z0-9-]+')
        ->name('api.products.show');
});
