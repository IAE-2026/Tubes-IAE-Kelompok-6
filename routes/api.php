<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Middleware\APIKeyMiddleware;

Route::prefix('v1')->middleware(['iae.auth'])->group(function () {
    Route::get('/locations', [LocationController::class, 'index']);
    Route::get('/locations/{id}', [LocationController::class, 'show']);
    Route::post('/locations', [LocationController::class, 'store']);
});