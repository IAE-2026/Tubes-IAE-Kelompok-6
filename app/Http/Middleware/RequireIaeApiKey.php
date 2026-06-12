<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireIaeApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-IAE-KEY');

        if ($apiKey === null || $apiKey === '') {
            return $this->error('Tidak terotorisasi: header X-IAE-KEY wajib dikirim', 401);
        }

        if (! in_array($apiKey, $this->validApiKeys(), true)) {
            return $this->error('Akses ditolak: API Key tidak valid', 403);
        }

        return $next($request);
    }

    private function validApiKeys(): array
    {
        $keys = config('services.smart_parking.valid_api_keys', []);

        return array_values(array_filter(array_map('trim', $keys)));
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => null,
        ], $status);
    }
}
