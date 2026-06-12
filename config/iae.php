<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IAE Central Corporate Mock Server (Cloud Dosen)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi integrasi ke tiga sistem terpusat yang disediakan dosen:
    |  1. SSO REST        -> POST /api/v1/auth/token, GET /api/v1/auth/jwks
    |  2. SOAP Audit      -> POST /soap/v1/audit            (Bearer)
    |  3. Message Broker  -> POST /api/v1/messages/publish  (Bearer)
    |
    | Semua nilai diambil dari .env agar kredensial tidak tertanam di kode.
    | Kredensial default mengikuti nomor absen 13.
    |
    */

    'base_url' => rtrim((string) env('IAE_BASE_URL', 'https://iae-sso.virtualfri.id'), '/'),

    // Identitas tim/mahasiswa
    'team_id' => env('IAE_TEAM_ID', 'TEAM-06'),

    // Modul 1 - Autentikasi Machine-to-Machine (M2M)
    'api_key' => env('IAE_API_KEY', 'KEY-MHS-185'),

    // Modul 1 - Autentikasi End-User (SSO Warga), dipakai jika ingin simulasi login warga
    'warga' => [
        'email' => env('IAE_WARGA_EMAIL', 'warga28@ktp.iae.id'),
        'password' => env('IAE_WARGA_PASSWORD', 'KtpDigital2026!'),
    ],

    // Path endpoint pusat (relatif terhadap base_url)
    'endpoints' => [
        'token' => env('IAE_TOKEN_PATH', '/api/v1/auth/token'),
        'jwks' => env('IAE_JWKS_PATH', '/api/v1/auth/jwks'),
        'soap_audit' => env('IAE_SOAP_PATH', '/soap/v1/audit'),
        'publish' => env('IAE_PUBLISH_PATH', '/api/v1/messages/publish'),
        'health' => env('IAE_HEALTH_PATH', '/health'),
    ],

    // Detik cache untuk JWKS dan token M2M
    'cache_ttl' => [
        'jwks' => (int) env('IAE_JWKS_CACHE_TTL', 3600),
        'm2m_token' => (int) env('IAE_M2M_TOKEN_CACHE_TTL', 240),
    ],

    // Timeout HTTP (detik)
    'timeout' => (int) env('IAE_HTTP_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Modul 2 - SOAP Audit
    |--------------------------------------------------------------------------
    */
    'soap' => [
        'envelope_ns' => 'http://schemas.xmlsoap.org/soap/envelope/',
        'iae_ns' => 'http://iae.central/audit',
        // ActivityName default untuk transaksi kritis (pembayaran)
        'activity_name' => env('IAE_SOAP_ACTIVITY', 'PaymentProcessed'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Modul 3 - Message Broker (RabbitMQ via HTTP gateway dosen)
    |--------------------------------------------------------------------------
    */
    'broker' => [
        'exchange' => env('IAE_BROKER_EXCHANGE', 'iae.central.exchange'),
        'routing_key' => env('IAE_BROKER_ROUTING_KEY', 'transaksi.pembayaran.selesai'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Modul 1 - Pemetaan Role (Federated Identity -> Role Lokal)
    |--------------------------------------------------------------------------
    |
    | Klaim dari JWT Cloud Dosen dipetakan ke role lokal Service B.
    | 'claim_map' mencocokkan nilai klaim (role/scope/realm) ke role lokal,
    | 'token_type_map' memetakan berdasar tipe token (m2m / user),
    | 'default' dipakai jika tidak ada yang cocok.
    |
    */
    'roles' => [
        // role lokal yang diizinkan melakukan transaksi kritis (pembayaran).
        // Catatan: 'customer' (warga SSO) diizinkan menyelesaikan pembayaran
        // parkirnya sendiri (model self-service). Gerbang otorisasi tetap
        // berlaku: request TANPA JWT valid dari SSO dosen akan ditolak.
        'allowed_to_pay' => ['finance-admin', 'cashier', 'service-account', 'customer'],

        // map nilai klaim (role/scope) -> role lokal
        'claim_map' => [
            'admin' => 'finance-admin',
            'finance' => 'finance-admin',
            'cashier' => 'cashier',
            'kasir' => 'cashier',
            'operator' => 'cashier',
            'warga' => 'customer',
            'citizen' => 'customer',
            'user' => 'customer',
        ],

        // map tipe token -> role lokal default
        'token_type_map' => [
            'm2m' => 'service-account',
            'machine' => 'service-account',
            'client' => 'service-account',
            'user' => 'customer',
            'end-user' => 'customer',
        ],

        'default' => 'customer',
    ],

];
