<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SsoAuthController;
use App\Http\Middleware\APIKeyMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes - Service A: Lahan & Lokasi
|--------------------------------------------------------------------------
|
| Tugas 2: CRUD Lokasi Parkir (dilindungi API Key)
| Tugas 3: SSO Login, SOAP Audit, AMQP Publisher
|
*/

// === SSO Authentication Routes (Tugas 3 - Modul 1) ===
// Endpoint login tidak perlu middleware auth (untuk mendapatkan token)
Route::prefix('v1/sso')->group(function () {
    Route::post('/login', [SsoAuthController::class, 'login']);
    Route::post('/login-m2m', [SsoAuthController::class, 'loginM2M']);

    // Endpoint /me dilindungi oleh SSO JWT middleware
    Route::middleware(['iae.sso'])->group(function () {
        Route::get('/me', [SsoAuthController::class, 'me']);
    });
});

// === Location CRUD Routes (Bearer Token) ===
Route::prefix('v1')->middleware(['iae.sso'])->group(function () {
    Route::get('/locations', [LocationController::class, 'index']);
    Route::get('/locations/{id}', [LocationController::class, 'show']);
    Route::post('/locations', [LocationController::class, 'store']);
    Route::post('/locations/{id}/occupy', [LocationController::class, 'occupy']);
    Route::post('/locations/{id}/release', [LocationController::class, 'release']);
    Route::post('/events/rabbitmq-callback', [LocationController::class, 'handleEventCallback']);
});