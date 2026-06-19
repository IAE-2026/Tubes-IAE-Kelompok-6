<?php

namespace App\Http\Controllers;

use App\Services\SmartParkingGateway;
use App\Services\TransactionStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    private const SERVICE_NAME = 'Transaksi-Pembayaran-Service';
    private const API_VERSION = 'v1';
    private const STATUS_BERLANGSUNG = 'BERLANGSUNG';
    private const STATUS_SUDAH_CHECKOUT = 'SUDAH_CHECKOUT';
    private const STATUS_SELESAI = 'SELESAI';

    public function index(TransactionStore $store): JsonResponse
    {
        $transactions = $store->all();

        return $this->success('Data berhasil diambil', $transactions, [
            'total' => count($transactions),
        ]);
    }

    public function show(TransactionStore $store, string $id): JsonResponse
    {
        $transaction = $store->find($id);

        if ($transaction === null) {
            return $this->error("Transaksi '{$id}' tidak ditemukan", 404);
        }

        return $this->success('Data berhasil diambil', $transaction);
    }

    public function store(Request $request, TransactionStore $store, SmartParkingGateway $gateway): JsonResponse
    {
        if ($request->filled('transaction_id') && $request->filled('action')) {
            return $this->handleAction($request, $store, $gateway);
        }

        $validator = Validator::make($request->all(), [
            'location_id' => ['required', 'string'],
            'member_card_id' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 400, $validator->errors()->toArray());
        }

        $locationId = $request->string('location_id')->toString();
        $memberCardId = $request->filled('member_card_id') ? $request->string('member_card_id')->toString() : null;

        $location = $gateway->getLocation($locationId);

        if ($location === null) {
            return $this->error("Lokasi '{$locationId}' tidak ditemukan", 404);
        }

        if ((int) ($location['available_spots'] ?? 0) <= 0) {
            return $this->error('Tidak ada slot parkir tersedia pada lokasi ini', 400);
        }

        $member = $gateway->getMember($memberCardId);

        if ($memberCardId !== null && $member === null) {
            return $this->error("Anggota '{$memberCardId}' tidak ditemukan", 404);
        }

        if ($member !== null && ! in_array(($member['status'] ?? null), ['aktif', 'active'], true)) {
            return $this->error("Anggota '{$memberCardId}' tidak aktif", 400);
        }

        $occupiedSpot = $gateway->occupySpot($locationId);
        $now = now()->toISOString();
        $newTransaction = [
            'id' => $store->nextId(),
            'location_id' => $locationId,
            'member_card_id' => $memberCardId,
            'entry_time' => $now,
            'exit_time' => null,
            'duration_hours' => null,
            'base_rate' => null,
            'benefit' => null,
            'total_amount' => null,
            'status' => self::STATUS_BERLANGSUNG,
            'payment_method' => null,
            'voucher_code' => null,
            'created_at' => $now,
        ];

        $store->create($newTransaction);

        return $this->success('Transaksi berhasil dibuat (tapping masuk)', $newTransaction, [
            'location_info' => [
                'id' => $location['id'] ?? $locationId,
                'name' => $location['name'] ?? null,
                'available_spots' => $occupiedSpot['available_spots'] ?? max(((int) ($location['available_spots'] ?? 1)) - 1, 0),
            ],
            'member_info' => $member === null ? null : [
                'id' => $member['id'] ?? $memberCardId,
                'name' => $member['name'] ?? null,
                'status' => $member['status'] ?? null,
            ],
        ], 201);
    }

    public function checkout(Request $request, TransactionStore $store, SmartParkingGateway $gateway, string $id): JsonResponse
    {
        $transaction = $store->find($id);

        if ($transaction === null) {
            return $this->error("Transaksi '{$id}' tidak ditemukan", 404);
        }

        if ($this->isCompleted($transaction)) {
            return $this->error('Transaksi sudah selesai', 400);
        }

        $location = $gateway->getLocation((string) $transaction['location_id']);
        $baseRate = (float) ($location['base_rate'] ?? 5000);
        $exitTime = now();
        $entryTime = \Carbon\Carbon::parse((string) $transaction['entry_time']);
        $durationHours = max((int) ceil($entryTime->diffInMinutes($exitTime) / 60), 1);
        $subtotal = $baseRate * $durationHours;
        $benefit = 0.0;

        if (! empty($transaction['member_card_id'])) {
            $member = $gateway->getMember((string) $transaction['member_card_id']);

            if ($member !== null && in_array(($member['status'] ?? null), ['aktif', 'active'], true)) {
                $benefit += round($subtotal * ((float) ($member['discount_percent'] ?? 0)) / 100);
            }
        }

        $voucherCode = $request->filled('voucher_code') ? $request->string('voucher_code')->toString() : null;

        if ($voucherCode !== null) {
            $voucher = $gateway->validateVoucher($voucherCode, $subtotal);

            if ($voucher === null || ($voucher['valid'] ?? false) !== true) {
                return $this->error("Voucher '{$voucherCode}' tidak valid atau tidak tersedia", 400);
            }

            $benefit += (float) ($voucher['discount_amount'] ?? 0);
        }

        $totalAmount = max($subtotal - $benefit, 0);
        $updated = $store->update($id, [
            'exit_time' => $exitTime->toISOString(),
            'duration_hours' => $durationHours,
            'base_rate' => $baseRate,
            'benefit' => $benefit,
            'total_amount' => $totalAmount,
            'status' => self::STATUS_SUDAH_CHECKOUT,
            'voucher_code' => $voucherCode,
        ]);

        return $this->success('Checkout berhasil dihitung', $updated, [
            'calculation' => [
                'base_rate_per_hour' => $baseRate,
                'duration_hours' => $durationHours,
                'subtotal' => $subtotal,
                'benefit' => $benefit,
                'total_amount' => $totalAmount,
            ],
        ]);
    }

    public function pay(Request $request, TransactionStore $store, SmartParkingGateway $gateway, string $id): JsonResponse
    {
        $transaction = $store->find($id);

        if ($transaction === null) {
            return $this->error("Transaksi '{$id}' tidak ditemukan", 404);
        }

        if ($this->isCompleted($transaction)) {
            return $this->error('Transaksi sudah selesai', 400);
        }

        if (empty($transaction['exit_time'])) {
            return $this->error('Silakan checkout terlebih dahulu sebelum melakukan pembayaran', 400);
        }

        $gateway->releaseSpot((string) $transaction['location_id']);
        $gateway->useVoucher($transaction['voucher_code'] ?? null);
        $gateway->recordMemberUsage($transaction['member_card_id'] ?? null, $id, $transaction['voucher_code'] ?? null);

        $updated = $store->update($id, [
            'status' => self::STATUS_SELESAI,
            'payment_method' => $request->input('payment_method', 'tunai'),
            'paid_at' => now()->toISOString(),
        ]);

        return $this->success('Pembayaran berhasil diselesaikan', $updated);
    }

    public function action(Request $request, TransactionStore $store, SmartParkingGateway $gateway, string $id): JsonResponse
    {
        $action = strtoupper($request->string('action')->toString());

        return match ($action) {
            'TAP_OUT', 'CHECKOUT' => $this->checkout($request, $store, $gateway, $id),
            'PAYMENT_SUCCESS', 'PAY' => $this->pay($request, $store, $gateway, $id),
            default => $this->error("Action '{$action}' tidak didukung", 400, [
                'action' => ['Gunakan TAP_OUT atau PAYMENT_SUCCESS.'],
            ]),
        };
    }

    private function handleAction(Request $request, TransactionStore $store, SmartParkingGateway $gateway): JsonResponse
    {
        $id = $request->string('transaction_id')->toString();
        $action = strtoupper($request->string('action')->toString());

        return match ($action) {
            'TAP_OUT', 'CHECKOUT' => $this->checkout($request, $store, $gateway, $id),
            'PAYMENT_SUCCESS', 'PAY' => $this->pay($request, $store, $gateway, $id),
            default => $this->error("Action '{$action}' tidak didukung", 400, [
                'action' => ['Gunakan TAP_OUT atau PAYMENT_SUCCESS.'],
            ]),
        };
    }

    private function isCompleted(array $transaction): bool
    {
        return in_array(($transaction['status'] ?? null), [self::STATUS_SELESAI, 'COMPLETED'], true);
    }

    private function success(string $message, mixed $data, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'meta' => array_merge([
                'service_name' => self::SERVICE_NAME,
                'api_version' => self::API_VERSION,
            ], $meta),
        ], $status);
    }

    private function error(string $message, int $status, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
