<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Memverifikasi JWT RS256 yang diterbitkan Cloud Dosen menggunakan JWKS (Modul 1).
 *
 * Alur: ambil JWKS dari IaeSsoClient -> parse menjadi key set -> JWT::decode.
 * Jika verifikasi pertama gagal karena 'kid' tidak ditemukan, JWKS di-refresh
 * sekali (key rotation) lalu dicoba ulang.
 */
class JwtVerifier
{
    public function __construct(private readonly IaeSsoClient $sso)
    {
    }

    /**
     * @return array<string,mixed>|null  Klaim JWT bila valid, null bila gagal.
     */
    public function verify(string $token): ?array
    {
        // beri sedikit toleransi clock-skew
        JWT::$leeway = 60;

        $claims = $this->attempt($token, $this->sso->jwks());

        if ($claims === null) {
            // mungkin key rotation: refresh JWKS lalu coba lagi sekali
            $claims = $this->attempt($token, $this->sso->jwks(fresh: true));
        }

        return $claims;
    }

    /**
     * @param  array<string,mixed>|null  $jwks
     * @return array<string,mixed>|null
     */
    private function attempt(string $token, ?array $jwks): ?array
    {
        if (empty($jwks['keys'])) {
            return null;
        }

        try {
            $keys = JWK::parseKeySet($jwks);
            $decoded = JWT::decode($token, $keys);

            return json_decode(json_encode($decoded), true);
        } catch (Throwable $e) {
            Log::info('Verifikasi JWT gagal', ['message' => $e->getMessage()]);

            return null;
        }
    }
}
