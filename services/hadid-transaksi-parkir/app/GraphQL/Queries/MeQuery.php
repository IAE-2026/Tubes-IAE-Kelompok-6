<?php

namespace App\GraphQL\Queries;

use App\Support\IaeIdentity;

final class MeQuery
{
    /**
     * Kembalikan identitas SSO yang telah di-resolve middleware (Modul 1).
     *
     * @return array<string,mixed>
     */
    public function __invoke(): array
    {
        $identity = app()->bound(IaeIdentity::class)
            ? app(IaeIdentity::class)
            : IaeIdentity::guest();

        return $identity->toArray();
    }
}
