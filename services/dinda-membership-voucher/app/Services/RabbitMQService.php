<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RabbitMQService
{
    private string $apiKey;
    private string $nim;
    private string $ssoBaseUrl;
    private string $teamId;

    public function __construct()
    {
        $this->apiKey = env('IAE_API_KEY', 'KEY-MHS-45');
        $this->nim = env('IAE_NIM', '102022400023');
        $this->ssoBaseUrl = rtrim(env('IAE_SSO_URL', 'https://iae-sso.virtualfri.id'), '/');
        $this->teamId = env('IAE_TEAM_ID', 'TEAM-06');
    }

    public function publish(string $routingKey, array $data, ?string $token = null): void
    {
        try {
            $m2mToken = $this->getM2MToken();

            if (!$m2mToken) {
                Log::error("RabbitMQ API Gagal. Token M2M tidak tersedia untuk event {$routingKey}");
                return;
            }

            // Kita wajib menggunakan HTTP API karena host AMQP native (iae-mq) sedang tidak bisa diakses / down dari network Anda,
            // dan port 5672 di iae-sso menolak kredensial mahasiswa/rahasia.
            
            // Format disesuaikan agar API Gateway Dosen meneruskannya
            // ke RabbitMQ dengan atribut properties AMQP yang benar (app_id dan exchange)
            $payload = [
                'routing_key' => $routingKey,
                'exchange' => 'iae.central.exchange', // Target exchange
                'properties' => [
                    'app_id' => $this->teamId // Agar muncul "Dari: TEAM-06" di Dashboard
                ],
                'message' => $data
            ];

            // Tembak API Dosen menggunakan Token M2M
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $m2mToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$this->ssoBaseUrl}/api/v1/messages/publish", $payload);

            if ($response->successful()) {
                Log::info("RabbitMQ API Publish Sukses: Event {$routingKey}");
            } else {
                Log::error("RabbitMQ API Gagal. Status: " . $response->status() . " Response: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Koneksi ke API Publisher gagal: ' . $e->getMessage());
        }
    }

    private function getM2MToken(): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->post("{$this->ssoBaseUrl}/api/v1/auth/token", [
                    'api_key' => $this->apiKey,
                    'nim' => $this->nim,
                ]);

            if (!$response->successful()) {
                Log::error('RabbitMQ API Gagal mendapat token M2M. Status: ' . $response->status() . ' Response: ' . $response->body());
                return null;
            }

            return $response->json('access_token')
                ?? $response->json('token')
                ?? $response->json('data.token');
        } catch (\Throwable $e) {
            Log::error('RabbitMQ API gagal koneksi ke endpoint M2M: ' . $e->getMessage());
            return null;
        }
    }
}
