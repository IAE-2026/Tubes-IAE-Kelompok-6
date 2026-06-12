<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AuditReceipt;

class SoapAuditService
{
    protected string $ssoUrl;
    protected string $teamId;

    public function __construct()
    {
        $this->ssoUrl = rtrim(env('IAE_SSO_URL', 'https://iae-sso.virtualfri.id'), '/');
        $this->teamId = env('IAE_TEAM_ID', 'TEAM-06');
    }

    /**
     * Kirim SOAP audit ke endpoint dosen, simpan receipt ke DB.
     */
    public function sendAudit(string $activityName, array $transactionData, string $referenceId, string $bearerToken): array
    {
        $jsonContent = json_encode($transactionData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $soapXml = $this->buildSoapEnvelope($activityName, $jsonContent);

        Log::info('[SOAP] Sending audit', ['activity' => $activityName, 'ref' => $referenceId]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'Authorization' => 'Bearer ' . $bearerToken,
                'SOAPAction' => 'http://iae.central/audit/AuditRequest',
            ])
            ->timeout(30)
            ->withBody($soapXml, 'text/xml')
            ->post("{$this->ssoUrl}/soap/v1/audit");

            $responseBody = $response->body();
            Log::info('[SOAP] Response', ['status' => $response->status()]);

            $result = $this->parseSoapResponse($responseBody);

            AuditReceipt::create([
                'transaction_type' => $activityName,
                'reference_id' => $referenceId,
                'receipt_number' => $result['receipt_number'] ?? 'PARSE_ERROR',
                'status' => $result['status'] ?? ($response->successful() ? 'SUCCESS' : 'FAILED'),
                'soap_request' => $soapXml,
                'soap_response' => $responseBody,
            ]);

            return [
                'success' => $response->successful(),
                'receipt_number' => $result['receipt_number'] ?? null,
                'status' => $result['status'] ?? null,
                'http_status' => $response->status(),
                'soap_request_raw' => $soapXml,
                'soap_response_raw' => $responseBody,
            ];
        } catch (\Exception $e) {
            Log::error('[SOAP] Error', ['error' => $e->getMessage(), 'activity' => $activityName]);

            AuditReceipt::create([
                'transaction_type' => $activityName,
                'reference_id' => $referenceId,
                'receipt_number' => 'ERROR',
                'status' => 'FAILED',
                'soap_request' => $soapXml,
                'soap_response' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'receipt_number' => null,
                'status' => 'ERROR',
                'error' => $e->getMessage(),
                'soap_request_raw' => $soapXml,
                'soap_response_raw' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build SOAP XML Envelope sesuai skema IAE.
     */
    protected function buildSoapEnvelope(string $activityName, string $logContent): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">' . "\n" .
            '  <soap:Body>' . "\n" .
            '    <iae:AuditRequest>' . "\n" .
            '      <iae:TeamID>' . htmlspecialchars($this->teamId) . '</iae:TeamID>' . "\n" .
            '      <iae:ActivityName>' . htmlspecialchars($activityName) . '</iae:ActivityName>' . "\n" .
            '      <iae:LogContent><![CDATA[' . $logContent . ']]></iae:LogContent>' . "\n" .
            '    </iae:AuditRequest>' . "\n" .
            '  </soap:Body>' . "\n" .
            '</soap:Envelope>';
    }

    /**
     * Parse SOAP response XML → extract Status & ReceiptNumber.
     */
    protected function parseSoapResponse(string $responseXml): array
    {
        $result = ['status' => null, 'receipt_number' => null];

        try {
            $cleanXml = preg_replace('/(<\/?)\\w+:/', '$1', $responseXml);
            $xml = @simplexml_load_string($cleanXml);

            if ($xml !== false) {
                $result['receipt_number'] = $this->findInXml($xml, 'ReceiptNumber');
                $result['status'] = $this->findInXml($xml, 'Status');
            }

            // Fallback: regex
            if (empty($result['receipt_number']) && preg_match('/ReceiptNumber[^>]*>([^<]+)</', $responseXml, $m)) {
                $result['receipt_number'] = $m[1];
            }
            if (empty($result['status']) && preg_match('/Status[^>]*>([^<]+)</', $responseXml, $m)) {
                $result['status'] = $m[1];
            }
        } catch (\Exception $e) {
            Log::warning('[SOAP] Parse error', ['error' => $e->getMessage()]);
        }

        return $result;
    }

    /**
     * Recursively search for a tag value in SimpleXMLElement.
     */
    protected function findInXml($xml, string $tagName): ?string
    {
        foreach ($xml->children() as $name => $child) {
            if ($name === $tagName) return (string) $child;
            $found = $this->findInXml($child, $tagName);
            if ($found !== null) return $found;
        }
        return null;
    }
}
