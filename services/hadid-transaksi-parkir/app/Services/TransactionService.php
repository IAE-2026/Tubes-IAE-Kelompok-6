<?php

namespace App\Services;

use Carbon\Carbon;
use GraphQL\Error\Error;

/**
 * Logika domain transaksi parkir Smart Parking, dipakai bersama oleh
 * resolver GraphQL (Tugas 3) maupun controller REST (legacy Tugas 2).
 *
 * Tiga tahap siklus hidup transaksi:
 *   tap-in  -> BERLANGSUNG
 *   checkout-> SUDAH_CHECKOUT (biaya dihitung)
 *   pay     -> SELESAI        (TRANSAKSI KRITIS / state-changing keuangan)
 */
class TransactionService
{
    public const STATUS_BERLANGSUNG = 'BERLANGSUNG';
    public const STATUS_SUDAH_CHECKOUT = 'SUDAH_CHECKOUT';
    public const STATUS_SELESAI = 'SELESAI';

    public function __construct(
        private readonly TransactionStore $store,
        private readonly SmartParkingGateway $gateway,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function tapIn(string $locationId, ?string $memberCardId = null): array
    {
        $location = $this->gateway->getLocation($locationId);

        if ($location === null) {
            throw new Error("Lokasi '{$locationId}' tidak ditemukan.");
        }

        if ((int) ($location['available_spots'] ?? 0) <= 0) {
            throw new Error('Tidak ada slot parkir tersedia pada lokasi ini.');
        }

        $member = $this->gateway->getMember($memberCardId);

        if ($memberCardId !== null && $member === null) {
            throw new Error("Anggota '{$memberCardId}' tidak ditemukan.");
        }

        if ($member !== null && ! in_array(($member['status'] ?? null), ['aktif', 'active'], true)) {
            throw new Error("Anggota '{$memberCardId}' tidak aktif.");
        }

        $this->gateway->occupySpot($locationId);
        $now = now()->toISOString();

        return $this->store->create([
            'id' => $this->store->nextId(),
            'location_id' => $locationId,
            'member_card_id' => $memberCardId,
            'entry_time' => $now,
            'status' => self::STATUS_BERLANGSUNG,
            'created_at' => $now,
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    public function checkout(string $id, ?string $voucherCode = null): array
    {
        $transaction = $this->store->find($id);

        if ($transaction === null) {
            throw new Error("Transaksi '{$id}' tidak ditemukan.");
        }

        if ($this->isCompleted($transaction)) {
            throw new Error('Transaksi sudah selesai.');
        }

        $location = $this->gateway->getLocation((string) $transaction['location_id']);
        $baseRate = (float) ($location['base_rate'] ?? 5000);
        $exitTime = now();
        $entryTime = Carbon::parse((string) $transaction['entry_time']);
        $durationHours = max((int) ceil($entryTime->diffInMinutes($exitTime) / 60), 1);
        $subtotal = $baseRate * $durationHours;
        $benefit = 0.0;

        if (! empty($transaction['member_card_id'])) {
            $member = $this->gateway->getMember((string) $transaction['member_card_id']);

            if ($member !== null && in_array(($member['status'] ?? null), ['aktif', 'active'], true)) {
                $benefit += round($subtotal * ((float) ($member['discount_percent'] ?? 0)) / 100);
            }
        }

        if ($voucherCode !== null && $voucherCode !== '') {
            $voucher = $this->gateway->validateVoucher($voucherCode, $subtotal);

            if ($voucher !== null && ($voucher['valid'] ?? false) === true) {
                $benefit += (float) ($voucher['discount_amount'] ?? 0);
            }
        }

        $totalAmount = max($subtotal - $benefit, 0);

        return $this->store->update($id, [
            'exit_time' => $exitTime->toISOString(),
            'duration_hours' => $durationHours,
            'base_rate' => $baseRate,
            'benefit' => $benefit,
            'total_amount' => $totalAmount,
            'status' => self::STATUS_SUDAH_CHECKOUT,
            'voucher_code' => $voucherCode,
        ]);
    }

    /**
     * Selesaikan pembayaran (transaksi kritis). Mengembalikan transaksi terbaru.
     *
     * @return array<string,mixed>
     */
    public function pay(string $id, string $paymentMethod = 'tunai', ?string $processedBy = null): array
    {
        $transaction = $this->store->find($id);

        if ($transaction === null) {
            throw new Error("Transaksi '{$id}' tidak ditemukan.");
        }

        if ($this->isCompleted($transaction)) {
            throw new Error('Transaksi sudah selesai.');
        }

        if (empty($transaction['exit_time'])) {
            throw new Error('Lakukan checkout (TAP_OUT) terlebih dahulu sebelum pembayaran.');
        }

        $this->gateway->releaseSpot((string) $transaction['location_id']);
        $this->gateway->useVoucher($transaction['voucher_code'] ?? null);
        $this->gateway->recordMemberUsage($transaction['member_card_id'] ?? null, $id, $transaction['voucher_code'] ?? null);

        return $this->store->update($id, [
            'status' => self::STATUS_SELESAI,
            'payment_method' => $paymentMethod,
            'paid_at' => now()->toISOString(),
            'processed_by' => $processedBy,
        ]);
    }

    /**
     * @param  array<string,mixed>  $attributes
     * @return array<string,mixed>|null
     */
    public function update(string $id, array $attributes): ?array
    {
        return $this->store->update($id, $attributes);
    }

    /**
     * @param  array<string,mixed>  $transaction
     */
    private function isCompleted(array $transaction): bool
    {
        return in_array(($transaction['status'] ?? null), [self::STATUS_SELESAI, 'COMPLETED'], true);
    }
}
