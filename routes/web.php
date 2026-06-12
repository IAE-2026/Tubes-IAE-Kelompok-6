<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $docsUrl = url('/api-docs');
    $openapiUrl = url('/openapi.json');
    $graphqlUrl = url('/graphiql');
    $healthUrl = url('/health');
    $transactionsUrl = url('/api/v1/transactions');

    $html = <<<HTML
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Service B - Transaksi & Pembayaran</title>
  <style>
    :root {
      --bg: #0f1419;
      --panel: #1a2128;
      --panel-soft: #232c35;
      --line: #2f3a44;
      --text: #f2f6fa;
      --muted: #9aa7b3;
      --green: #00c781;
      --blue: #49a3ff;
      --orange: #ff9f43;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Inter, sans-serif;
      background: radial-gradient(circle at top left, #1a2128 0%, #0f1419 70%);
      color: var(--text);
      min-height: 100vh;
    }
    .container {
      max-width: 960px;
      margin: 0 auto;
      padding: 56px 28px 80px;
    }
    .badge {
      display: inline-block;
      padding: 6px 14px;
      border-radius: 999px;
      background: rgba(0, 199, 129, 0.15);
      color: var(--green);
      font-size: 13px;
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-bottom: 18px;
    }
    h1 {
      margin: 0 0 14px;
      font-size: 44px;
      font-weight: 700;
      letter-spacing: -0.5px;
      line-height: 1.1;
    }
    h1 span { color: var(--green); }
    .lead {
      margin: 0 0 36px;
      font-size: 17px;
      color: var(--muted);
      max-width: 680px;
      line-height: 1.6;
    }
    .meta {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 16px;
      margin-bottom: 40px;
    }
    .meta div {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 10px;
      padding: 14px 16px;
    }
    .meta label {
      display: block;
      color: var(--muted);
      font-size: 12px;
      letter-spacing: 0.6px;
      text-transform: uppercase;
      margin-bottom: 6px;
    }
    .meta strong { font-size: 15px; }
    h2 {
      font-size: 14px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: var(--muted);
      margin: 32px 0 16px;
    }
    .links {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 14px;
    }
    .links a {
      display: block;
      padding: 18px 20px;
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 10px;
      text-decoration: none;
      color: var(--text);
      transition: all 0.18s ease;
    }
    .links a:hover {
      border-color: var(--green);
      transform: translateY(-2px);
      background: var(--panel-soft);
    }
    .links a .title { font-weight: 600; font-size: 16px; display: block; margin-bottom: 4px; }
    .links a .desc { font-size: 13px; color: var(--muted); }
    .links a.green { border-left: 4px solid var(--green); }
    .links a.blue { border-left: 4px solid var(--blue); }
    .links a.orange { border-left: 4px solid var(--orange); }
    code {
      background: rgba(255,255,255,0.07);
      padding: 2px 8px;
      border-radius: 5px;
      font-family: "Fira Code", Consolas, Monaco, monospace;
      font-size: 13px;
      color: var(--green);
    }
    footer {
      margin-top: 56px;
      padding-top: 24px;
      border-top: 1px solid var(--line);
      color: var(--muted);
      font-size: 13px;
    }
  </style>
</head>
<body>
  <div class="container">
    <span class="badge">Service Online</span>
    <h1>Smart Parking <span>/ Service B</span></h1>
    <p class="lead">REST + GraphQL API untuk mengelola transaksi parkir: pencatatan tapping in, kalkulasi keluar, dan penyelesaian pembayaran. Dibangun dengan Laravel 13 dan MySQL 8.</p>

    <div class="meta">
      <div><label>Mahasiswa</label><strong>Hadid Hamar</strong></div>
      <div><label>NIM / X-IAE-KEY</label><strong>102022400126</strong></div>
      <div><label>Framework</label><strong>Laravel 13 · PHP 8.4</strong></div>
      <div><label>Resource</label><strong>transactions</strong></div>
    </div>

    <h2>Dokumentasi</h2>
    <div class="links">
      <a class="green" href="{$docsUrl}">
        <span class="title">Swagger UI</span>
        <span class="desc">Dokumentasi OpenAPI interaktif</span>
      </a>
      <a class="blue" href="{$graphqlUrl}">
        <span class="title">GraphQL Playground</span>
        <span class="desc">Query transaksi via GraphiQL</span>
      </a>
      <a class="orange" href="{$openapiUrl}">
        <span class="title">OpenAPI JSON</span>
        <span class="desc">Spesifikasi mentah</span>
      </a>
    </div>

    <h2>Endpoint Cepat</h2>
    <div class="links">
      <a href="{$transactionsUrl}">
        <span class="title">GET /api/v1/transactions</span>
        <span class="desc">Daftar transaksi (wajib header <code>X-IAE-KEY</code>)</span>
      </a>
      <a href="{$healthUrl}">
        <span class="title">GET /health</span>
        <span class="desc">Health check service</span>
      </a>
    </div>

    <footer>
      Semua endpoint <code>/api/v1/*</code> wajib menyertakan header <code>X-IAE-KEY: 102022400126</code>.
      Tugas 2 - BBK2HAB3 Integrasi Aplikasi Enterprise · Kelompok 6.
    </footer>
  </div>
</body>
</html>
HTML;

    return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Service B (Transaksi & Pembayaran) sedang berjalan',
        'data' => [
            'service' => 'Transaksi-Pembayaran-Service',
            'version' => 'v1',
            'framework' => 'Laravel',
            'timestamp' => now()->toISOString(),
        ],
    ]);
});
