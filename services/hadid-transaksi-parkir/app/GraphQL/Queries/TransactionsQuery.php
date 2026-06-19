<?php

namespace App\GraphQL\Queries;

use App\Services\TransactionStore;

final class TransactionsQuery
{
    public function __construct(private readonly TransactionStore $store)
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function __invoke(): array
    {
        return $this->store->all();
    }
}
