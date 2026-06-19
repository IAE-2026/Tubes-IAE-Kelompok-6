<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Klien REST ke Central SSO Cloud Dosen (Modul 1).
 *
 * Tanggung jawab:
 *  - Mengambil JWKS (public key RS256) untuk memverifikasi JWT masuk.
 *  - Menukar API key (M2M) / kredensial warga menjadi access token JWT.
 *
 * Access token M2M dipakai sebagai Bearer untuk memanggil endpoint
 * SOAP audit (Modul 2) dan message publish (Modul 3).
 */
class IaeSsoClient
{
    private function baseUrl(): string
    {
        return rtrim((string) config('iae.base_url'), '/');
    }

    private function url(string $key): string
    {
        return $this->baseUrl().config("iae.endpoints.{$key}");
    }

    private function timeout(): int
    {
        return (int) config('iae.timeout', 15);
    }

    /**
     * Ambil JWKS (di-cache). Mengembalikan struktur ['keys' => [...]].
     *
     * @return array<string,mixed>|null
     */
    public function jwks(bool $fresh = false): ?array
    {
        $ttl = (int) config('iae.cache_ttl.jwks', 3600);

        if ($fresh) {
            Cache::forget('iae:jwks');
        }

        return Cache::remember('iae:jwks', $ttl, function (): ?array {
            try {
                $response = Http::timeout($this->timeout())
                    ->acceptJson()
                    ->get($this->url('jwks'));

                if (! $response->successful()) {
                    Log::warning('IAE JWKS fetch gagal', ['status' => $response->status()]);

                    return null;
                }

                $json = $response->json();

                // Toleransi beberapa bentuk: {keys:[...]} atau langsung [...]
                if (isset($json['keys'])) {
                    return $json;
                }

                if (is_array($json) && array_is_list($json)) {
                    return ['keys' => $json];
                }

                return $json;
            } catch (Throwable $e) {
                Log::error('IAE JWKS exception', ['message' => $e->getMessage()]);

                return null;
            }
        });
    }

    /**
     * Access token Machine-to-Machine (di-cache singkat) untuk dipakai
     * sebagai Bearer ke endpoint SOAP & publish.
     */
    public function m2mToken(bool $fresh = false): ?string
    {
        $ttl = (int) config('iae.cache_ttl.m2m_token', 240);

        if ($fresh) {
            Cache::forget('iae:m2m_token');
        }

       return Cache::remember('iae:m2m_token', $ttl, function (): ?string {
    return $this->requestToken([
        'api_key' => config('iae.api_key'),
        'nim' => config('iae.nim'),
    ]);
});

    /**
     * Access token end-user (SSO Warga). Tidak di-cache.
     */
    public function userToken(?string $email = null, ?string $password = null): ?string
    {
        return $this->requestToken([
            'email' => $email ?? config('iae.warga.email'),
            'password' => $password ?? config('iae.warga.password'),
        ]);
    }

    /**
     * Tukar payload kredensial menjadi access token.
     *
     * @param  array<string,mixed>  $payload
     */
    public function requestToken(array $payload): ?string
    {
        try {
            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->asJson()
                ->post($this->url('token'), $payload);

            if (! $response->successful()) {
                Log::warning('IAE token request gagal', [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);

                return null;
            }

            $json = $response->json();

            // Toleransi nama field: access_token / token / data.access_token
            return $json['access_token']
                ?? $json['token']
                ?? data_get($json, 'data.access_token')
                ?? data_get($json, 'data.token');
        } catch (Throwable $e) {
            Log::error('IAE token exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Health check ke server pusat (dipakai resolver/diagnostik).
     *
     * @return array<string,mixed>|null
     */
    public function health(): ?array
    {
        try {
            $response = Http::timeout($this->timeout())->acceptJson()->get($this->url('health'));

            return $response->successful() ? (array) $response->json() : null;
        } catch (Throwable) {
            return null;
        }
    }
}
