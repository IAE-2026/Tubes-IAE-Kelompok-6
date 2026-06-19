<?php

namespace App\Services;

use App\Models\Transaction;

class TransactionStore
{
    public function all(): array
    {
        return Transaction::orderBy('created_at')->get()->map(fn (Transaction $t) => $this->toArray($t))->all();
    }

    public function find(string $id): ?array
    {
        $transaction = Transaction::find($id);

        return $transaction ? $this->toArray($transaction) : null;
    }

    public function create(array $attributes): array
    {
        $attributes['id'] = $attributes['id'] ?? $this->nextId();

        $transaction = Transaction::create($attributes);

        return $this->toArray($transaction);
    }

    public function update(string $id, array $attributes): ?array
    {
        $transaction = Transaction::find($id);

        if ($transaction === null) {
            return null;
        }

        $transaction->fill($attributes);
        $transaction->save();

        return $this->toArray($transaction);
    }

    public function nextId(): string
    {
        $max = 0;

        foreach (Transaction::pluck('id') as $id) {
            $number = (int) preg_replace('/\D+/', '', (string) $id);
            $max = max($max, $number);
        }

        return 'trx_'.str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    private function toArray(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'location_id' => $transaction->location_id,
            'member_card_id' => $transaction->member_card_id,
            'entry_time' => optional($transaction->entry_time)->toISOString(),
            'exit_time' => optional($transaction->exit_time)->toISOString(),
            'duration_hours' => $transaction->duration_hours !== null ? (float) $transaction->duration_hours : null,
            'base_rate' => $transaction->base_rate !== null ? (float) $transaction->base_rate : null,
            'benefit' => $transaction->benefit !== null ? (float) $transaction->benefit : null,
            'total_amount' => $transaction->total_amount !== null ? (float) $transaction->total_amount : null,
            'status' => $transaction->status,
            'payment_method' => $transaction->payment_method,
            'voucher_code' => $transaction->voucher_code,
            'paid_at' => optional($transaction->paid_at)->toISOString(),
            'audit_receipt_number' => $transaction->audit_receipt_number,
            'audit_status' => $transaction->audit_status,
            'event_published_status' => $transaction->event_published_status,
            'processed_by' => $transaction->processed_by,
            'created_at' => optional($transaction->created_at)->toISOString(),
        ];
    }
}
