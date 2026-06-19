<?php

namespace App\GraphQL\Queries;

use App\Services\IaeSsoClient;

final class HealthQuery
{
    public function __construct(private readonly IaeSsoClient $sso)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function __invoke(): array
    {
        return [
            'service' => 'Transaksi-Pembayaran-Service',
            'status' => 'success',
            'framework' => 'Laravel + Lighthouse',
            'graphql' => 'ready',
            'central_reachable' => $this->sso->health() !== null,
            'timestamp' => now()->toISOString(),
        ];
    }
}
