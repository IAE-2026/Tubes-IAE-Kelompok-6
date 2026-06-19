<?php

namespace App\GraphQL\Mutations;

use App\Services\MessageBrokerClient;
use App\Services\SoapAuditClient;
use App\Services\TransactionService;
use App\Support\IaeIdentity;
use GraphQL\Error\Error;

/**
 * TRANSAKSI KRITIS - payTransaction.
 *
 * Orkestrasi tiga lapis sistem terpusat dosen secara berurutan:
 *   1. Modul 1 (SSO)   : verifikasi identitas + cek role lokal yang berhak.
 *   2. Modul 2 (SOAP)  : kirim audit XML, simpan ReceiptNumber.
 *   3. Modul 3 (RabbitMQ): broadcast event PaymentProcessed.
 */
final class PayTransaction
{
    public function __construct(
        private readonly TransactionService $service,
        private readonly SoapAuditClient $soap,
        private readonly MessageBrokerClient $broker,
    ) {
    }

    /**
     * @param  array<string,mixed>  $args
     * @return array<string,mixed>
     */
    public function __invoke($root, array $args): array
    {
        $identity = app()->bound(IaeIdentity::class) ? app(IaeIdentity::class) : IaeIdentity::guest();

        // --- Modul 1: gerbang otorisasi berbasis SSO + role lokal ---
        if (! $identity->authenticated) {
            throw new Error(
                'Tidak terotorisasi: sertakan header "Authorization: Bearer <JWT dari SSO dosen>". '
                .($identity->error ?? '')
            );
        }

        if (! $identity->canPay()) {
            $allowed = implode(', ', config('iae.roles.allowed_to_pay', []));
            throw new Error(
                "Akses ditolak: role lokal '{$identity->localRole}' tidak berhak menyelesaikan pembayaran. "
                ."Role yang diizinkan: {$allowed}."
            );
        }

        $id = (string) $args['id'];
        $paymentMethod = (string) ($args['payment_method'] ?? 'tunai');
        $processedBy = $identity->email ?? $identity->subject;

        // Eksekusi transaksi kritis (state-changing keuangan)
        $transaction = $this->service->pay($id, $paymentMethod, $processedBy);

        // --- Modul 2: SOAP audit untuk transaksi kritis ---
        $logContent = [
            'transaction_id' => $transaction['id'],
            'event' => 'PaymentProcessed',
            'location_id' => $transaction['location_id'],
            'member_card_id' => $transaction['member_card_id'],
            'duration_hours' => $transaction['duration_hours'],
            'base_rate' => $transaction['base_rate'],
            'benefit' => $transaction['benefit'],
            'total_amount' => $transaction['total_amount'],
            'payment_method' => $transaction['payment_method'],
            'voucher_code' => $transaction['voucher_code'],
            'paid_at' => $transaction['paid_at'],
            'processed_by' => $processedBy,
        ];

        $audit = $this->soap->send(
            (string) config('iae.soap.activity_name', 'PaymentProcessed'),
            $logContent,
            $transaction['id'],
        );

        // --- Modul 3: broadcast event ke RabbitMQ ---
        $event = $this->broker->publish('PaymentProcessed', [
            'transaction_id' => $transaction['id'],
            'total_amount' => $transaction['total_amount'],
            'payment_method' => $transaction['payment_method'],
            'location_id' => $transaction['location_id'],
            'audit_receipt_number' => $audit['receipt_number'],
            'paid_at' => $transaction['paid_at'],
        ]);

        // Persist bukti integrasi pada transaksi
        $transaction = $this->service->update($id, [
            'audit_receipt_number' => $audit['receipt_number'],
            'audit_status' => $audit['status'],
            'event_published_status' => $event['ok'] ? 'PUBLISHED' : 'FAILED',
        ]) ?? $transaction;

        return [
            'status' => 'success',
            'message' => 'Pembayaran selesai. Audit SOAP & broadcast event diproses.',
            'transaction' => $transaction,
            'audit' => [
                'ok' => $audit['ok'],
                'receipt_number' => $audit['receipt_number'],
                'status' => $audit['status'],
                'http_status' => $audit['http_status'],
                'error' => $audit['error'],
            ],
            'event' => [
                'ok' => $event['ok'],
                'message_id' => $event['message_id'],
                'http_status' => $event['http_status'],
                'error' => $event['error'],
                'response' => is_string($event['response'] ?? null)
                    ? $event['response']
                    : json_encode($event['response'] ?? null),
            ],
        ];
    }
}
