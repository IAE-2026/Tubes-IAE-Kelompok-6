<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Menerjemahkan klaim JWT eksternal menjadi role lokal Service B, lalu
 * mem-provision (find-or-create) user federasi beserta relasi role-nya (Modul 1).
 *
 * Inilah inti "memetakan user dari Cloud Dosen ke tabel roles lokal".
 */
class RoleMapper
{
    /**
     * Provision user dari klaim JWT dan kembalikan [User, namaRoleLokal].
     *
     * @param  array<string,mixed>  $claims
     * @return array{0: User, 1: string}
     */
    public function provision(array $claims): array
    {
        $tokenType = $this->inferTokenType($claims);
        $roleName = $this->resolveRoleName($claims, $tokenType);

        $subject = (string) ($claims['sub'] ?? $claims['client_id'] ?? $claims['email'] ?? Str::uuid());

        $user = User::updateOrCreate(
            ['iae_subject' => $subject],
            [
                'name' => $claims['name'] ?? $claims['preferred_username'] ?? $claims['client_id'] ?? null,
                'email' => $claims['email'] ?? null,
                'token_type' => $tokenType,
                'last_login_at' => now(),
            ]
        );

        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['description' => $this->describe($roleName)]
        );

        // sinkronkan tanpa menghapus role lain yang mungkin sudah dimiliki
        $user->roles()->syncWithoutDetaching([$role->id]);
        $user->setRelation('roles', $user->roles()->get());

        return [$user, $roleName];
    }

    /**
     * Tentukan role lokal dari klaim role/scope; fallback ke tipe token; lalu default.
     *
     * @param  array<string,mixed>  $claims
     */
    public function resolveRoleName(array $claims, string $tokenType): string
    {
        $candidates = $this->collectRoleCandidates($claims);
        $claimMap = (array) config('iae.roles.claim_map', []);

        // claim_map terurut dari privilege tertinggi -> ambil match pertama
        foreach ($claimMap as $needle => $localRole) {
            if (in_array(Str::lower((string) $needle), $candidates, true)) {
                return $localRole;
            }
        }

        $tokenMap = (array) config('iae.roles.token_type_map', []);
        if (isset($tokenMap[$tokenType])) {
            return $tokenMap[$tokenType];
        }

        return (string) config('iae.roles.default', 'customer');
    }

    /**
     * Kumpulkan semua nilai klaim yang mungkin merepresentasikan role/scope.
     *
     * @param  array<string,mixed>  $claims
     * @return array<int,string>  lowercase
     */
    private function collectRoleCandidates(array $claims): array
    {
        $values = [];

        foreach (['role', 'roles', 'scope', 'scopes', 'authorities', 'groups'] as $key) {
            $raw = $claims[$key] ?? null;

            if (is_string($raw)) {
                // scope sering berupa string dipisah spasi
                $values = array_merge($values, preg_split('/[\s,]+/', $raw) ?: []);
            } elseif (is_array($raw)) {
                $values = array_merge($values, $raw);
            }
        }

        // Keycloak-style nested claims
        $values = array_merge($values, (array) Arr::get($claims, 'realm_access.roles', []));
        foreach ((array) Arr::get($claims, 'resource_access', []) as $resource) {
            $values = array_merge($values, (array) Arr::get($resource, 'roles', []));
        }

        return array_values(array_unique(array_map(
            fn ($v) => Str::lower((string) $v),
            array_filter($values, fn ($v) => is_scalar($v))
        )));
    }

    /**
     * @param  array<string,mixed>  $claims
     */
    public function inferTokenType(array $claims): string
    {
        // tipe eksplisit bila ada
        foreach (['token_type', 'type', 'typ'] as $key) {
            if (! empty($claims[$key]) && is_string($claims[$key])) {
                $val = Str::lower($claims[$key]);
                if (in_array($val, ['m2m', 'machine', 'client', 'user', 'end-user'], true)) {
                    return $val;
                }
            }
        }

        // heuristik: kredensial M2M biasanya tanpa email, dengan client_id / api_key,
        // atau subject berformat KEY-MHS-XX
        $sub = (string) ($claims['sub'] ?? '');
        if (! empty($claims['client_id']) || ! empty($claims['api_key']) || Str::startsWith($sub, 'KEY-')) {
            return 'm2m';
        }

        if (! empty($claims['email'])) {
            return 'user';
        }

        return 'user';
    }

    private function describe(string $roleName): string
    {
        return match ($roleName) {
            'finance-admin' => 'Admin keuangan - boleh menyelesaikan pembayaran & memicu audit.',
            'cashier' => 'Kasir - boleh menyelesaikan pembayaran transaksi parkir.',
            'service-account' => 'Akun layanan (M2M) - integrasi antar-service.',
            'customer' => 'Warga/pengguna - hanya boleh melihat & memulai transaksi.',
            default => 'Role lokal Service B.',
        };
    }
}
