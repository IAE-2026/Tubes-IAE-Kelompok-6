<?php

namespace App\Support;

use App\Models\User;

/**
 * Identitas terverifikasi hasil Modul 1 (Federated SSO).
 *
 * Objek ini di-bind ke container pada setiap request oleh middleware
 * ResolveIaeIdentity, lalu dikonsumsi resolver GraphQL untuk mengecek
 * otorisasi role lokal sebelum menjalankan transaksi kritis.
 */
class IaeIdentity
{
    /**
     * @param  array<string,mixed>  $claims
     */
    public function __construct(
        public readonly bool $authenticated = false,
        public readonly ?string $subject = null,
        public readonly ?string $email = null,
        public readonly ?string $name = null,
        public readonly ?string $tokenType = null,
        public readonly ?string $localRole = null,
        public readonly array $claims = [],
        public readonly ?User $user = null,
        public readonly ?string $error = null,
        public readonly ?string $token = null,
    ) {
    }

    public static function guest(?string $error = null): self
    {
        return new self(authenticated: false, error: $error);
    }

    /**
     * Apakah role lokal user diizinkan menjalankan transaksi kritis (pembayaran)?
     */
    public function canPay(): bool
    {
        if (! $this->authenticated || $this->localRole === null) {
            return false;
        }

        return in_array($this->localRole, config('iae.roles.allowed_to_pay', []), true);
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'authenticated' => $this->authenticated,
            'subject' => $this->subject,
            'email' => $this->email,
            'name' => $this->name,
            'token_type' => $this->tokenType,
            'local_role' => $this->localRole,
            'can_pay' => $this->canPay(),
        ];
    }
}
