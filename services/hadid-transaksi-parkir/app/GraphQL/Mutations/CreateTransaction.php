<?php

namespace App\GraphQL\Mutations;

use App\Services\TransactionService;

final class CreateTransaction
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
        $input = $args['input'];

        $transaction = $this->service->tapIn(
            (string) $input['location_id'],
            $input['member_card_id'] ?? null,
        );

        return [
            'status' => 'success',
            'message' => 'Transaksi berhasil dibuat (tapping masuk).',
            'transaction' => $transaction,
        ];
    }
}
