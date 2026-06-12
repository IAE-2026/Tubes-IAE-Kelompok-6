<?php

namespace App\Http\Middleware;

use App\Services\JwtVerifier;
use App\Services\RoleMapper;
use App\Support\IaeIdentity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Modul 1 - Federated SSO.
 *
 * Untuk setiap request GraphQL: jika ada header `Authorization: Bearer <jwt>`,
 * JWT diverifikasi terhadap JWKS Cloud Dosen, klaim dipetakan ke role lokal,
 * dan user federasi di-provision ke tabel users/roles. Hasilnya (IaeIdentity)
 * di-bind ke container agar bisa dibaca resolver GraphQL.
 *
 * Verifikasi bersifat non-blocking: tanpa token, request tetap lanjut sebagai
 * guest (query publik tetap jalan). Penegakan role dilakukan di resolver
 * mutation kritis (payTransaction).
 */
class ResolveIaeIdentity
{
    public function __construct(
        private readonly JwtVerifier $verifier,
        private readonly RoleMapper $mapper,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $identity = $this->resolve($request);

        // bind sebagai instance agar resolver bisa app(IaeIdentity::class)
        app()->instance(IaeIdentity::class, $identity);
        $request->attributes->set('iae_identity', $identity);

        return $next($request);
    }

    private function resolve(Request $request): IaeIdentity
    {
        $token = $this->bearerToken($request);

        if ($token === null) {
            return IaeIdentity::guest();
        }

        $claims = $this->verifier->verify($token);

        if ($claims === null) {
            return IaeIdentity::guest('Token tidak valid atau gagal diverifikasi terhadap JWKS dosen.');
        }

        [$user, $roleName] = $this->mapper->provision($claims);

        // login-kan ke guard agar @guard/auth() Lighthouse juga konsisten
        auth()->setUser($user);

        return new IaeIdentity(
            authenticated: true,
            subject: (string) ($claims['sub'] ?? $user->iae_subject),
            email: $claims['email'] ?? $user->email,
            name: $claims['name'] ?? $user->name,
            tokenType: $user->token_type,
            localRole: $roleName,
            claims: $claims,
            user: $user,
            token: $token,
        );
    }

    private function bearerToken(Request $request): ?string
    {
        $header = (string) $request->header('Authorization', '');

        if (preg_match('/Bearer\s+(.+)$/i', $header, $m)) {
            return trim($m[1]);
        }

        // fallback: header kustom untuk kemudahan pengujian
        $alt = $request->header('X-IAE-TOKEN');

        return $alt ? trim($alt) : null;
    }
}
