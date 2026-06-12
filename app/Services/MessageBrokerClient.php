<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Modul 3 - AMQP Publisher.
 *
 * Menyebarkan event bisnis (mis. PaymentProcessed) secara asinkron ke
 * message broker pusat (RabbitMQ exchange `iae.central.exchange`) melalui
 * HTTP gateway dosen: POST /api/v1/messages/publish (Bearer).
 *
 * Payload dikirim sebagai JSON; kegagalan tidak menggagalkan transaksi
 * (fire-and-forget) namun statusnya dicatat.
 */
class MessageBrokerClient
{
    public function __construct(private readonly IaeSsoClient $sso)
    {
    }

    /**
     * Publish event ke exchange pusat.
     *
     * @param  array<string,mixed>  $payload  isi event (bebas, JSON)
     * @return array{ok: bool, http_status: ?int, response: mixed, error: ?string, message_id: string}
     */
    public function publish(string $eventName, array $payload, ?string $routingKey = null): array
    {
        $messageId = (string) Str::uuid();

        $teamId = (string) config('iae.team_id');

        $identity = app()->bound(\App\Support\IaeIdentity::class)
            ? app(\App\Support\IaeIdentity::class)
            : null;

        // "message" = OBJECT event, mengikuti format yang ditampilkan papan dosen
        // (event_name, service_name, occurred_at, data domain, approved_by).
        $message = array_merge([
            'event_name' => $eventName,
            'service_name' => 'Transaksi-Pembayaran-Service',
            'api_version' => 'v1',
            'message_id' => $messageId,
            'occurred_at' => now()->toISOString(),
        ], $payload, [
            'approved_by' => [
                'sso_subject' => $identity?->email ?? $identity?->subject,
                'roles' => $identity && $identity->localRole ? [$identity->localRole] : [],
            ],
        ]);

        // Papan terikat ke exchange iae.central.exchange (binding "#": semua
        // routing key tampil). team_id dikirim agar papan menampilkan "Dari: <team>".
        $envelope = [
            'exchange' => (string) config('iae.broker.exchange'),
            'routing_key' => $routingKey ?? (string) config('iae.broker.routing_key'),
            'team_id' => $teamId,
            'message' => $message,
        ];

        $token = $this->outboundToken();

        if ($token === null) {
            return [
                'ok' => false,
                'http_status' => null,
                'response' => null,
                'error' => 'Gagal memperoleh Bearer token (caller/M2M) untuk auth ke server pusat.',
                'message_id' => $messageId,
            ];
        }

        try {
            $url = rtrim((string) config('iae.base_url'), '/').config('iae.endpoints.publish');

            $response = Http::timeout((int) config('iae.timeout', 15))
                ->withToken($token)
                ->acceptJson()
                ->asJson()
                ->post($url, $envelope);

            if (! $response->successful()) {
                Log::warning('Publish RabbitMQ gagal', ['status' => $response->status(), 'body' => $response->body()]);
            }

            return [
                'ok' => $response->successful(),
                'http_status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
                'error' => $response->successful() ? null : ('HTTP '.$response->status().': '.$response->body()),
                'message_id' => $messageId,
            ];
        } catch (Throwable $e) {
            Log::error('Publish RabbitMQ exception', ['message' => $e->getMessage()]);

            return [
                'ok' => false,
                'http_status' => null,
                'response' => null,
                'error' => $e->getMessage(),
                'message_id' => $messageId,
            ];
        }
    }

    /**
     * Bearer untuk panggilan ke server pusat: pakai token caller (JWT yang
     * sedang login) bila ada, jika tidak fallback ke token M2M.
     */
    private function outboundToken(): ?string
    {
        // Utamakan token M2M (API key KEY-MHS-185) agar event tercatat atas
        // subjek API-Key kita di dashboard dosen. Fallback ke token caller (warga).
        $m2m = $this->sso->m2mToken();
        if ($m2m !== null) {
            return $m2m;
        }

        $identity = app()->bound(\App\Support\IaeIdentity::class)
            ? app(\App\Support\IaeIdentity::class)
            : null;

        return $identity?->token;
    }
}
