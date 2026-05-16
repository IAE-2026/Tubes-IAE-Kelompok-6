<?php

use App\Http\Controllers\Api\V1\MembershipController;
use App\Http\Middleware\VerifyApiKey;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Service C (Keanggotaan & Voucher)
|--------------------------------------------------------------------------
|
| Semua endpoint diproteksi menggunakan middleware VerifyApiKey
| yang memvalidasi header X-IAE-KEY.
|
*/

Route::prefix('v1')->middleware(VerifyApiKey::class)->group(function () {

    // ── Keanggotaan (Membership) ──────────────────────────────────────
    Route::get('/memberships', [MembershipController::class, 'index']);
    Route::get('/memberships/{id}', [MembershipController::class, 'show']);
    Route::post('/memberships', [MembershipController::class, 'store']);
});
