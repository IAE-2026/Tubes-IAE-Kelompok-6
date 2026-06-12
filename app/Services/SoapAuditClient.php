<?php

namespace App\Services;

use App\Models\AuditLog;
use DOMDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Modul 2 - SOAP XML Client.
 *
 * Mengubah (transformasi) data transaksi berbentuk JSON/array menjadi SOAP
 * Envelope XML yang kaku, mengirimkannya ke endpoint audit legacy dosen
 * (POST /soap/v1/audit, Bearer), lalu mem-parse dan menyimpan ReceiptNumber
 * yang dikembalikan server.
 */
class SoapAuditClient
{
    public function __construct(private readonly IaeSsoClient $sso)
    {
    }

    /**
     * Kirim audit untuk satu aktivitas bisnis.
     *
     * @param  array<string,mixed>  $logContent  data transaksi aktual (akan jadi <LogContent> CDATA JSON)
     * @return array{ok: bool, receipt_number: ?string, status: ?string, http_status: ?int, error: ?string, audit_log_id: int}
     */
    public function send(string $activityName, array $logContent, ?string $transactionId = null): array
    {
        $teamId = (string) config('iae.team_id', 'TEAM-06');
        $envelope = $this->buildEnvelope($teamId, $activityName, $logContent);

        $audit = AuditLog::create([
            'transaction_id' => $transactionId,
            'activity_name' => $activityName,
            'team_id' => $teamId,
            'log_content' => $logContent,
            'soap_request' => $envelope,
            'status' => 'PENDING',
        ]);

        $token = $this->outboundToken();

        if ($token === null) {
            $audit->update(['status' => 'ERROR', 'error_message' => 'Gagal memperoleh Bearer token (caller/M2M) untuk auth ke server pusat.']);

            return $this->result(false, $audit, error: 'Gagal memperoleh Bearer token.');
        }

        try {
            $url = rtrim((string) config('iae.base_url'), '/').config('iae.endpoints.soap_audit');

            $response = Http::timeout((int) config('iae.timeout', 15))
                ->withToken($token)
                ->withHeaders([
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Accept' => 'application/xml, text/xml',
                    'SOAPAction' => 'AuditRequest',
                ])
                ->withBody($envelope, 'text/xml')
                ->post($url);

            $body = $response->body();
            $receipt = $this->extractTag($body, 'ReceiptNumber');
            $status = $this->extractTag($body, 'Status');

            $ok = $response->successful() && $receipt !== null;

            $audit->update([
                'soap_response' => $body,
                'receipt_number' => $receipt,
                'status' => $status ?? ($response->successful() ? 'SUCCESS' : 'FAILED'),
                'http_status' => $response->status(),
                'error_message' => $ok ? null : 'Respons tidak mengandung ReceiptNumber.',
            ]);

            return $this->result($ok, $audit->fresh());
        } catch (Throwable $e) {
            Log::error('SOAP audit exception', ['message' => $e->getMessage()]);
            $audit->update(['status' => 'ERROR', 'error_message' => $e->getMessage()]);

            return $this->result(false, $audit->fresh(), error: $e->getMessage());
        }
    }

    /**
     * Bangun SOAP Envelope XML yang kaku sesuai skema dosen.
     *
     * @param  array<string,mixed>  $logContent
     */
    public function buildEnvelope(string $teamId, string $activityName, array $logContent): string
    {
        $soapNs = (string) config('iae.soap.envelope_ns');
        $iaeNs = (string) config('iae.soap.iae_ns');

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $envelope = $dom->createElementNS($soapNs, 'soap:Envelope');
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:iae', $iaeNs);
        $dom->appendChild($envelope);

        $body = $dom->createElementNS($soapNs, 'soap:Body');
        $envelope->appendChild($body);

        $request = $dom->createElementNS($iaeNs, 'iae:AuditRequest');
        $body->appendChild($request);

        $request->appendChild($dom->createElementNS($iaeNs, 'iae:TeamID', $teamId));
        $request->appendChild($dom->createElementNS($iaeNs, 'iae:ActivityName', $activityName));

        // LogContent dibungkus CDATA berisi JSON (transformasi JSON -> XML kaku)
        $logEl = $dom->createElementNS($iaeNs, 'iae:LogContent');
        $json = json_encode($logContent, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $logEl->appendChild($dom->createCDATASection($json));
        $request->appendChild($logEl);

        return $dom->saveXML();
    }

    /**
     * Ambil isi sebuah tag dari respons XML, abaikan prefix namespace
     * (mis. <iae:ReceiptNumber> atau <ReceiptNumber>).
     */
    private function extractTag(string $xml, string $localName): ?string
    {
        if ($xml === '') {
            return null;
        }

        if (preg_match('/<(?:[a-zA-Z0-9]+:)?'.preg_quote($localName, '/').'>(.*?)<\/(?:[a-zA-Z0-9]+:)?'.preg_quote($localName, '/').'>/s', $xml, $m)) {
            return trim(html_entity_decode($m[1]));
        }

        return null;
    }

    /**
     * Bearer untuk panggilan ke server pusat: pakai token caller (JWT yang
     * sedang login) bila ada, jika tidak fallback ke token M2M.
     */
    private function outboundToken(): ?string
    {
        // Utamakan token M2M (API key KEY-MHS-185) agar audit tercatat atas
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

    private function result(bool $ok, AuditLog $audit, ?string $error = null): array
    {
        return [
            'ok' => $ok,
            'receipt_number' => $audit->receipt_number,
            'status' => $audit->status,
            'http_status' => $audit->http_status,
            'error' => $error ?? $audit->error_message,
            'audit_log_id' => $audit->id,
        ];
    }
}
