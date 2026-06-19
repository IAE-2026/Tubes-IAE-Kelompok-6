<?php

use App\Http\Controllers\DocsController;
use App\Http\Controllers\GraphiqlController;
use App\Http\Controllers\TransactionController;
use App\Http\Middleware\RequireIaeApiKey;
use Illuminate\Support\Facades\Route;

Route::options('/{any}', fn () => response()->noContent())->where('any', '.*');

/*
|--------------------------------------------------------------------------
| Endpoint GraphQL (Tugas 3 - fokus utama)
|--------------------------------------------------------------------------
| POST /graphql di-handle otomatis oleh Lighthouse (lihat config/lighthouse.php).
| Di sini kita hanya menyediakan halaman GraphiQL untuk eksplorasi schema,
| lengkap dengan input header Authorization Bearer JWT (Modul 1 SSO).
*/
Route::get('/graphiql', [GraphiqlController::class, 'playground']);

/*
|--------------------------------------------------------------------------
| Endpoint REST legacy (Tugas 2) - tetap dipertahankan
|--------------------------------------------------------------------------
*/
Route::get('/api-docs', [DocsController::class, 'swaggerUi']);
Route::get('/api-docs/', [DocsController::class, 'swaggerUi']);
Route::get('/openapi.json', [DocsController::class, 'openApi']);

Route::middleware(RequireIaeApiKey::class)->group(function (): void {
    Route::get('/api/v1/transactions', [TransactionController::class, 'index']);
    Route::get('/api/v1/transactions/{id}', [TransactionController::class, 'show']);
    Route::post('/api/v1/transactions', [TransactionController::class, 'store']);
    Route::post('/api/v1/transactions/{id}', [TransactionController::class, 'action']);
    Route::post('/api/v1/transactions/{id}/checkout', [TransactionController::class, 'checkout']);
    Route::post('/api/v1/transactions/{id}/pay', [TransactionController::class, 'pay']);
});
