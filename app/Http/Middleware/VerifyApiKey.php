<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Daftar API Key yang diizinkan (NIM anggota kelompok).
     */
    private function getValidKeys(): array
    {
        $keys = env('IAE_API_KEYS', '102022400023,102022400039,102022400126');

        return array_filter(array_map('trim', explode(',', $keys)));
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-IAE-KEY');

        if (empty($apiKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak terotorisasi: header X-IAE-KEY wajib dikirim',
                'errors' => null,
            ], 401);
        }

        if (!in_array($apiKey, $this->getValidKeys(), true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak: API Key tidak valid',
                'errors' => null,
            ], 403);
        }

        return $next($request);
    }
}
