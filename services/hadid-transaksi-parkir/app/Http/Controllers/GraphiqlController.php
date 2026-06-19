<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Menyajikan halaman GraphiQL untuk endpoint Lighthouse (POST /graphql).
 * Menyediakan input "Authorization: Bearer <JWT>" agar memudahkan pengujian
 * Modul 1 (SSO) langsung dari browser.
 */
class GraphiqlController extends Controller
{
    public function playground(): Response
    {
        return response($this->html())->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function html(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>GraphiQL - IAE Tugas 3 (Service B)</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/graphiql/1.5.20/graphiql.min.css">
  <style>
    body { margin: 0; height: 100vh; display: flex; flex-direction: column; font-family: monospace; }
    #auth { display: flex; align-items: center; gap: 10px; padding: 10px 16px; background: #15181d; color: #e6edf3; flex-wrap: wrap; }
    #auth label { color: #8fb3ff; }
    #auth input { flex: 1; min-width: 320px; padding: 8px 10px; border: 1px solid #30363d; border-radius: 6px; background: #0d1117; color: #7ee787; }
    #auth .hint { color: #768390; font-size: 12px; width: 100%; }
    #graphiql { flex: 1; min-height: 0; }
  </style>
</head>
<body>
  <div id="auth">
    <label for="token">Authorization: Bearer</label>
    <input id="token" placeholder="tempel JWT hasil POST /api/v1/auth/token dari SSO dosen di sini">
    <span class="hint">Bearer JWT diperlukan untuk mutation kritis payTransaction (Modul 1). Query publik tidak memerlukannya.</span>
  </div>
  <div id="graphiql"></div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react/17.0.2/umd/react.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/17.0.2/umd/react-dom.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/graphiql/1.5.20/graphiql.min.js"></script>
  <script>
    function graphQLFetcher(params) {
      var headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
      var token = document.getElementById('token').value.trim();
      if (token) { headers['Authorization'] = 'Bearer ' + token; }
      return fetch('/graphql', {
        method: 'POST',
        headers: headers,
        body: JSON.stringify(params)
      }).then(function (r) { return r.json(); });
    }

    var defaultQuery = [
      '# IAE Tugas 3 - Service B Transaksi & Pembayaran (GraphQL)',
      '# 1) Cek identitas SSO + role lokal hasil pemetaan (Modul 1):',
      '{',
      '  me { authenticated email local_role can_pay }',
      '  transactions { id status total_amount audit_receipt_number }',
      '}',
      '',
      '# 2) Transaksi kritis (perlu Bearer JWT dengan role finance-admin/cashier):',
      '# mutation {',
      '#   payTransaction(id: "trx_001", payment_method: "qris") {',
      '#     status message',
      '#     transaction { id status audit_receipt_number event_published_status }',
      '#     audit { ok receipt_number status http_status }',
      '#     event { ok message_id http_status }',
      '#   }',
      '# }'
    ].join('\n');

    ReactDOM.render(
      React.createElement(GraphiQL, { fetcher: graphQLFetcher, defaultQuery: defaultQuery }),
      document.getElementById('graphiql')
    );
  </script>
</body>
</html>
HTML;
    }
}
