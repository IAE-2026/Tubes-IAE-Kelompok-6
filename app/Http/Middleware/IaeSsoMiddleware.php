<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use App\Models\Role;

class IaeSsoMiddleware
{
    /**
     * Middleware untuk memverifikasi JWT dari SSO Dosen (Cloud Pusat).
     *
     * 1. Menerima JWT dari header Authorization: Bearer <token>
     * 2. Mengambil public keys (JWKS) dari SSO Dosen
     * 3. Memverifikasi signature JWT menggunakan RS256
     * 4. Extract payload dan petakan ke role lokal
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Bearer token is missing',
                'errors' => null
            ], 401);
        }

        $token = substr($authHeader, 7);

        try {
            // Ambil JWKS (public keys) dari SSO Dosen, cache selama 1 jam
            $jwks = $this->getJwks();

            if (empty($jwks)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengambil JWKS dari SSO server',
                    'errors' => null
                ], 502);
            }

            // Decode & verify JWT menggunakan JWKS
            $decoded = $this->verifyJwt($token, $jwks);

            if (!$decoded) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired JWT token',
                    'errors' => null
                ], 401);
            }

            // Extract email dari payload
            $payload = (array) $decoded;
            $email = $payload['sub'] ?? $payload['email'] ?? null;

            // Cari role lokal berdasarkan email
            $localRole = null;
            if ($email) {
                $roleRecord = Role::where('email', $email)->first();
                $localRole = $roleRecord ? $roleRecord->role : 'viewer';
            }

            // Simpan data SSO ke request untuk digunakan controller
            $request->merge([
                'sso_user' => $payload,
                'sso_email' => $email,
                'sso_local_role' => $localRole,
                'sso_token' => $token,
            ]);

            Log::info('[SSO Middleware] JWT verified', [
                'email' => $email,
                'local_role' => $localRole,
            ]);

            return $next($request);
        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'JWT token has expired',
                'errors' => null
            ], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid JWT signature',
                'errors' => null
            ], 401);
        } catch (\Exception $e) {
            Log::error('[SSO Middleware] JWT verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'JWT verification failed: ' . $e->getMessage(),
                'errors' => null
            ], 401);
        }
    }

    /**
     * Mengambil JWKS (JSON Web Key Set) dari SSO Dosen.
     * Hasil di-cache selama 1 jam untuk performa.
     */
    protected function getJwks(): ?array
    {
        return Cache::remember('iae_sso_jwks', 3600, function () {
            $ssoUrl = rtrim(env('IAE_SSO_URL', 'https://iae-sso.virtualfri.id'), '/');

            try {
                // Coba endpoint utama dulu
                $response = Http::timeout(10)->get("{$ssoUrl}/api/v1/auth/jwks");

                if ($response->successful()) {
                    Log::info('[SSO] JWKS fetched from /api/v1/auth/jwks');
                    return $response->json();
                }

                // Fallback ke .well-known
                $response = Http::timeout(10)->get("{$ssoUrl}/.well-known/jwks.json");

                if ($response->successful()) {
                    Log::info('[SSO] JWKS fetched from /.well-known/jwks.json');
                    return $response->json();
                }
            } catch (\Exception $e) {
                Log::error('[SSO] Failed to fetch JWKS', [
                    'error' => $e->getMessage(),
                ]);
            }

            return null;
        });
    }

    /**
     * Verifikasi JWT token menggunakan JWKS.
     * Mendukung RS256 algorithm.
     */
    protected function verifyJwt(string $token, array $jwks): ?object
    {
        try {
            // Parse JWKS menjadi array Key yang dikenali oleh firebase/php-jwt
            $keys = JWK::parseKeySet($jwks);
            $decoded = JWT::decode($token, $keys);
            return $decoded;
        } catch (\Exception $e) {
            Log::warning('[SSO] JWT decode with JWK failed, trying manual', [
                'error' => $e->getMessage(),
            ]);

            // Fallback: manual key matching
            if (isset($jwks['keys']) && is_array($jwks['keys'])) {
                foreach ($jwks['keys'] as $keyData) {
                    try {
                        $alg = $keyData['alg'] ?? 'RS256';
                        $key = JWK::parseKey($keyData, $alg);
                        $decoded = JWT::decode($token, $key);
                        return $decoded;
                    } catch (\Exception $innerEx) {
                        continue;
                    }
                }
            }

            throw $e;
        }
    }
}
