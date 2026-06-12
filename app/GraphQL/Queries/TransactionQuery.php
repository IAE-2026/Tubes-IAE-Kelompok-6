<?php

namespace App\GraphQL\Queries;

use App\Services\TransactionStore;

final class TransactionQuery
{
    public function __construct(private readonly TransactionStore $store)
    {
    }

    /**
     * @param  array<string,mixed>  $args
     * @return array<string,mixed>|null
     */
    public function __invoke($root, array $args): ?array
    {
        return $this->store->find((string) $args['id']);
    }
}
