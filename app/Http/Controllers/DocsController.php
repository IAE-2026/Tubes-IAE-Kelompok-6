<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class DocsController extends Controller
{
    public function swaggerUi(): Response
    {
        return response($this->swaggerHtml())->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function openApi(): JsonResponse
    {
        return response()->json([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Service B - API Transaksi & Pembayaran',
                'version' => '1.0.0',
                'description' => 'Service Smart Parking untuk mengelola transaksi parkir, kalkulasi keluar, penyelesaian pembayaran, dan integrasi dengan service lokasi serta keanggotaan.',
            ],
            'servers' => [
                ['url' => config('app.url', 'http://localhost:3002')],
            ],
            'components' => [
                'securitySchemes' => [
                    'ApiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-IAE-KEY',
                    ],
                ],
                'schemas' => $this->schemas(),
            ],
            'security' => [
                ['ApiKeyAuth' => []],
            ],
            'paths' => $this->paths(),
        ]);
    }

    private function swaggerHtml(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dokumentasi API Service B</title>
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
  <style>
    :root {
      --bg: #171c1f;
      --panel: #1d252b;
      --panel-soft: #202b33;
      --line: #34424b;
      --text: #f2f6fa;
      --muted: #b7c2cc;
      --green: #00c781;
      --blue: #49a3ff;
    }

    html,
    body {
      margin: 0;
      min-height: 100%;
      background: var(--bg);
    }

    body {
      color: var(--text);
      font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .swagger-ui {
      color: var(--text);
    }

    .swagger-ui .topbar {
      display: none;
    }

    .swagger-ui .wrapper {
      max-width: none;
      padding: 0 64px;
    }

    .swagger-ui .information-container {
      background: var(--bg);
      padding: 56px 0 44px;
    }

    .swagger-ui .info {
      margin: 0;
    }

    .swagger-ui .info .title,
    .swagger-ui .info p,
    .swagger-ui .info li,
    .swagger-ui .info table {
      color: var(--text);
    }

    .swagger-ui .info .title {
      font-size: 46px;
      font-weight: 700;
      letter-spacing: 0;
    }

    .swagger-ui .info .title small {
      top: -8px;
    }

    .swagger-ui .info .title small pre {
      color: #fff;
    }

    .swagger-ui a.nostyle,
    .swagger-ui a.nostyle:visited,
    .swagger-ui .opblock-tag,
    .swagger-ui .opblock .opblock-summary-description,
    .swagger-ui .opblock .opblock-summary-path,
    .swagger-ui .opblock .opblock-summary-path__deprecated,
    .swagger-ui .opblock-description-wrapper p,
    .swagger-ui .response-col_status,
    .swagger-ui .response-col_description,
    .swagger-ui .parameter__name,
    .swagger-ui .parameter__type,
    .swagger-ui .parameter__in,
    .swagger-ui table thead tr td,
    .swagger-ui table thead tr th {
      color: var(--text);
    }

    .swagger-ui .scheme-container,
    .swagger-ui .opblock-tag-section {
      background: var(--bg);
      border-top: 1px solid rgba(255, 255, 255, 0.04);
      border-bottom: 1px solid var(--line);
      box-shadow: none;
    }

    .swagger-ui .scheme-container {
      padding: 48px 0 40px;
    }

    .swagger-ui .schemes > label,
    .swagger-ui .servers-title {
      color: var(--text);
    }

    .swagger-ui select {
      background: var(--panel);
      border-color: #a9b4bd;
      color: var(--text);
    }

    .swagger-ui .btn.authorize {
      border-color: var(--green);
      color: var(--green);
      background: transparent;
    }

    .swagger-ui .btn.authorize svg {
      fill: var(--green);
    }

    .swagger-ui .opblock-tag {
      border-bottom-color: var(--line);
      font-size: 30px;
      padding: 34px 12px 16px;
    }

    .swagger-ui .opblock {
      border-radius: 4px;
      box-shadow: none;
      margin: 0 0 18px;
    }

    .swagger-ui .opblock .opblock-summary {
      min-height: 48px;
    }

    .swagger-ui .opblock .opblock-section-header {
      background: var(--panel);
      box-shadow: none;
    }

    .swagger-ui .opblock .opblock-section-header h4,
    .swagger-ui .opblock .opblock-section-header label,
    .swagger-ui .opblock .opblock-section-header label span {
      color: var(--text);
    }

    .swagger-ui .opblock .opblock-body {
      background: var(--panel-soft);
      color: var(--text);
    }

    .swagger-ui .opblock.opblock-get {
      background: rgba(73, 163, 255, 0.12);
      border-color: var(--blue);
    }

    .swagger-ui .opblock.opblock-get .opblock-summary {
      border-color: var(--blue);
    }

    .swagger-ui .opblock.opblock-get .opblock-summary-method {
      background: var(--blue);
      color: #03111f;
    }

    .swagger-ui .opblock.opblock-post {
      background: rgba(0, 199, 129, 0.12);
      border-color: var(--green);
    }

    .swagger-ui .opblock.opblock-post .opblock-summary {
      border-color: var(--green);
    }

    .swagger-ui .opblock.opblock-post .opblock-summary-method {
      background: var(--green);
      color: #031a12;
    }

    .swagger-ui .opblock-summary-method {
      border-radius: 4px;
      font-weight: 700;
    }

    .swagger-ui textarea,
    .swagger-ui input[type=text],
    .swagger-ui input[type=password],
    .swagger-ui input[type=email] {
      background: #10161a;
      border-color: var(--line);
      color: var(--text);
    }

    .swagger-ui .model-box,
    .swagger-ui .model,
    .swagger-ui .prop-format,
    .swagger-ui .prop-type,
    .swagger-ui .tab li,
    .swagger-ui .responses-inner h4,
    .swagger-ui .responses-inner h5 {
      color: var(--text);
    }

    .swagger-ui .highlight-code,
    .swagger-ui .microlight {
      background: #10161a;
      color: var(--text);
    }

    @media (max-width: 900px) {
      .swagger-ui .wrapper {
        padding: 0 20px;
      }

      .swagger-ui .info .title {
        font-size: 34px;
      }
    }
  </style>
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
  <script>
    window.ui = SwaggerUIBundle({
      url: '/openapi.json',
      dom_id: '#swagger-ui',
      presets: [SwaggerUIBundle.presets.apis],
      layout: 'BaseLayout',
      docExpansion: 'list',
      defaultModelsExpandDepth: -1,
      displayRequestDuration: true
    });
  </script>
</body>
</html>
HTML;
    }

    private function paths(): array
    {
        return [
            '/api/v1/transactions' => [
                'get' => [
                    'summary' => 'Melihat riwayat seluruh transaksi parkir',
                    'tags' => ['Transaksi'],
                    'responses' => [
                        '200' => ['description' => 'Daftar transaksi berhasil diambil'],
                        '401' => ['description' => 'X-IAE-KEY tidak dikirim'],
                        '403' => ['description' => 'X-IAE-KEY tidak valid'],
                    ],
                ],
                'post' => [
                    'summary' => 'Membuat transaksi baru saat tapping masuk',
                    'tags' => ['Transaksi'],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/CreateTransactionRequest'],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => ['description' => 'Transaksi tapping in berhasil dibuat'],
                        '400' => ['description' => 'Validasi gagal'],
                        '404' => ['description' => 'Lokasi atau anggota tidak ditemukan'],
                    ],
                ],
            ],
            '/api/v1/transactions/{id}' => [
                'get' => [
                    'summary' => 'Melihat detail satu transaksi parkir beserta biayanya',
                    'tags' => ['Transaksi'],
                    'parameters' => [$this->idParameter()],
                    'responses' => [
                        '200' => ['description' => 'Detail transaksi berhasil diambil'],
                        '404' => ['description' => 'Transaksi tidak ditemukan'],
                    ],
                ],
                'post' => [
                    'summary' => 'Memicu action pembayaran saat keluar',
                    'description' => 'Kirim action TAP_OUT untuk kalkulasi checkout, atau PAYMENT_SUCCESS untuk menyelesaikan pembayaran.',
                    'tags' => ['Transaksi'],
                    'parameters' => [$this->idParameter()],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/TransactionActionRequest'],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Action berhasil diproses'],
                        '400' => ['description' => 'Action tidak valid atau transaksi sudah selesai'],
                        '404' => ['description' => 'Transaksi tidak ditemukan'],
                    ],
                ],
            ],
        ];
    }

    private function schemas(): array
    {
        return [
            'CreateTransactionRequest' => [
                'type' => 'object',
                'required' => ['location_id'],
                'properties' => [
                    'location_id' => ['type' => 'string', 'example' => 'loc_001'],
                    'member_card_id' => ['type' => 'string', 'example' => 'MEM001'],
                ],
            ],
            'TransactionActionRequest' => [
                'type' => 'object',
                'required' => ['action'],
                'properties' => [
                    'action' => ['type' => 'string', 'enum' => ['TAP_OUT', 'PAYMENT_SUCCESS'], 'example' => 'TAP_OUT'],
                    'voucher_code' => ['type' => 'string', 'example' => 'WELCOME50'],
                    'payment_method' => ['type' => 'string', 'example' => 'e-wallet'],
                ],
            ],
        ];
    }

    private function idParameter(): array
    {
        return [
            'name' => 'id',
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => 'string'],
            'example' => 'trx_001',
        ];
    }
}
