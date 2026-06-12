<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AmqpPublisherService
{
    protected string $ssoUrl;

    public function __construct()
    {
        $this->ssoUrl = rtrim(env('IAE_SSO_URL', 'https://iae-sso.virtualfri.id'), '/');
    }

    /**
     * Publish event ke RabbitMQ via SSO endpoint.
     */
    public function publish(string $eventName, array $eventData, string $bearerToken): array
    {
        $messageContent = [
            'event' => $eventName,
            'data' => $eventData,
            'timestamp' => now()->toIso8601String(),
            'source' => 'service-a-lahan-lokasi',
            'team_id' => env('IAE_TEAM_ID', 'TEAM-06'),
        ];

        $payload = [
            'message' => $messageContent,
            'routing_key' => $eventName,
        ];

        Log::info('[AMQP] Publish', ['event' => $eventName]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $bearerToken,
            ])
            ->timeout(15)
            ->post("{$this->ssoUrl}/api/v1/messages/publish", $payload);

            $responseBody = $response->json() ?? $response->body();

            Log::info('[AMQP] Response', ['status' => $response->status(), 'body' => $responseBody]);

            return [
                'success' => $response->successful(),
                'http_status' => $response->status(),
                'response' => $responseBody,
            ];
        } catch (\Exception $e) {
            Log::error('[AMQP] Error', ['error' => $e->getMessage(), 'event' => $eventName]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
