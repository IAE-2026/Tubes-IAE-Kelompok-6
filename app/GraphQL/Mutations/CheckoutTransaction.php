<?php

namespace App\GraphQL\Mutations;

use App\Services\TransactionService;

final class CheckoutTransaction
{
    public function __construct(private readonly TransactionService $service)
    {
    }

    /**
     * @param  array<string,mixed>  $args
     * @return array<string,mixed>
     */
    public function __invoke($root, array $args): array
    {
        $transaction = $this->service->checkout(
            (string) $args['id'],
            $args['voucher_code'] ?? null,
        );

        return [
            'status' => 'success',
            'message' => 'Checkout berhasil dihitung (tapping keluar).',
            'transaction' => $transaction,
        ];
    }
}
