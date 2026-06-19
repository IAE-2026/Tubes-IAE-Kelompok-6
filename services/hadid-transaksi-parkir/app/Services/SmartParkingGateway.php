<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class SmartParkingGateway
{
    public function getLocation(string $locationId): ?array
    {
        if ($this->serviceAUrl() !== null) {
            $payload = $this->get($this->serviceAUrl()."/api/v1/locations/{$locationId}");
            if ($payload !== null) {
                return $payload['data'] ?? $this->mockLocation($locationId);
            }
        }

        return $this->mockLocation($locationId);
    }

public function occupySpot(string $locationId): ?array
{
    if ($this->serviceAUrl() === null) {
        return null;
    }

    $payload = $this->post($this->serviceAUrl()."/api/v1/locations/{$locationId}/occupy", [
        'slots' => 1,
    ]);

    return $payload['data'] ?? null;
}

   public function releaseSpot(string $locationId): ?array
{
    if ($this->serviceAUrl() === null) {
        return null;
    }

    $payload = $this->post($this->serviceAUrl()."/api/v1/locations/{$locationId}/release", [
        'slots' => 1,
    ]);

    return $payload['data'] ?? null;
}

    public function getMember(?string $memberCardId): ?array
    {
        if ($memberCardId === null || $memberCardId === '') {
            return null;
        }

        if ($this->serviceCUrl() !== null) {
            $payload = $this->get($this->serviceCUrl()."/api/v1/memberships/{$memberCardId}");
            if ($payload !== null) {
                return $payload['data'] ?? $this->mockMember($memberCardId);
            }
        }

        return $this->mockMember($memberCardId);
    }

    public function validateVoucher(?string $code, float $subtotal): ?array
    {
        if ($code === null || $code === '' || $this->serviceCUrl() === null) {
            return null;
        }

        $payload = $this->post($this->serviceCUrl().'/api/v1/vouchers/validate', [
            'code' => $code,
            'subtotal' => $subtotal,
        ]);

        return $payload['data'] ?? null;
    }

    public function useVoucher(?string $code): ?array
    {
        if ($code === null || $code === '' || $this->serviceCUrl() === null) {
            return null;
        }

        $payload = $this->post($this->serviceCUrl()."/api/v1/vouchers/{$code}/use");

        return $payload['data'] ?? null;
    }

    public function recordMemberUsage(?string $memberCardId, string $transactionId, ?string $voucherCode): ?array
    {
        if ($memberCardId === null || $memberCardId === '' || $this->serviceCUrl() === null) {
            return null;
        }

        $payload = $this->post($this->serviceCUrl()."/api/v1/memberships/{$memberCardId}/record-usage", [
            'transaction_id' => $transactionId,
            'voucher_code' => $voucherCode,
        ]);

        return $payload['data'] ?? null;
    }

    private function get(string $url): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())->acceptJson()->get($url);

            return $response->successful() ? $response->json() : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function post(string $url, array $body = []): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())->acceptJson()->post($url, $body);

            return $response->successful() ? $response->json() : null;
        } catch (Throwable) {
            return null;
        }
    }

private function headers(): array
{
    $headers = [
        'X-IAE-KEY' => config('services.smart_parking.internal_api_key'),
    ];

    $token = app(IaeSsoClient::class)->m2mToken();

    if ($token !== null) {
        $headers['Authorization'] = 'Bearer '.$token;
    }

    return $headers;
}

    private function serviceAUrl(): ?string
    {
        $url = config('services.smart_parking.service_a_url');

        return $url ? rtrim($url, '/') : null;
    }

    private function serviceCUrl(): ?string
    {
        $url = config('services.smart_parking.service_c_url');

        return $url ? rtrim($url, '/') : null;
    }

    private function mockLocation(string $locationId): ?array
    {
        $locations = [
            'loc_001' => ['id' => 'loc_001', 'name' => 'Gedung A - Basement', 'available_spots' => 45, 'base_rate' => 5000],
            'loc_002' => ['id' => 'loc_002', 'name' => 'Mall Central - VIP', 'available_spots' => 12, 'base_rate' => 10000],
            'loc_003' => ['id' => 'loc_003', 'name' => 'Parkiran Terbuka', 'available_spots' => 78, 'base_rate' => 3000],
        ];

        return $locations[$locationId] ?? null;
    }

    private function mockMember(string $memberCardId): ?array
    {
        $members = [
            'MEM001' => ['id' => 'MEM001', 'name' => 'Budi Santoso', 'status' => 'aktif', 'discount_percent' => 20],
            'MEM002' => ['id' => 'MEM002', 'name' => 'Siti Rahma', 'status' => 'aktif', 'discount_percent' => 15],
            'MEM003' => ['id' => 'MEM003', 'name' => 'Ahmad Hidayat', 'status' => 'kedaluwarsa', 'discount_percent' => 0],
        ];

        return $members[$memberCardId] ?? null;
    }
}
