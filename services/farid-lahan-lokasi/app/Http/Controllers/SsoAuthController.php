<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Role;

class SsoAuthController extends Controller
{
    protected string $ssoUrl;

    public function __construct()
    {
        $this->ssoUrl = rtrim(env('IAE_SSO_URL', 'https://iae-sso.virtualfri.id'), '/');
    }

    /**
     * Login via SSO Dosen menggunakan email & password.
     * 
     * Forward credentials ke SSO Cloud Dosen dan return JWT token.
     * 
     * POST /api/v1/sso/login
     * Body: { "email": "warga20@ktp.iae.id", "password": "KtpDigital2026!" }
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $response = Http::timeout(15)->post("{$this->ssoUrl}/api/v1/auth/token", [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            $data = $response->json();

            if ($response->successful()) {
                Log::info('[SSO Login] User login sukses', [
                    'email' => $request->email,
                ]);

                // Pastikan user ada di tabel roles lokal
                $this->ensureLocalRole($request->email);

                return response()->json([
                    'status' => 'success',
                    'message' => 'SSO Login berhasil',
                    'data' => $data,
                    'meta' => [
                        'service_name' => 'Lahan-Lokasi-Service',
                        'api_version' => 'v1',
                        'sso_provider' => $this->ssoUrl,
                    ]
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'SSO Login gagal',
                'errors' => $data,
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('[SSO Login] Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghubungi SSO server: ' . $e->getMessage(),
                'errors' => null,
            ], 502);
        }
    }

    /**
     * Login Machine-to-Machine (M2M) menggunakan API Key.
     * 
     * POST /api/v1/sso/login-m2m
     * Body: { "api_key": "KEY-MHS-67" }
     */
    public function loginM2M(Request $request)
    {
        $apiKey = $request->input('api_key', env('IAE_API_KEY', 'KEY-MHS-67'));

        try {
            $response = Http::timeout(15)->post("{$this->ssoUrl}/api/v1/auth/token", [
                'api_key' => $apiKey,
            ]);

            $data = $response->json();

            if ($response->successful()) {
                Log::info('[SSO M2M] Login M2M sukses', [
                    'api_key' => $apiKey,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'SSO M2M Login berhasil',
                    'data' => $data,
                    'meta' => [
                        'service_name' => 'Lahan-Lokasi-Service',
                        'api_version' => 'v1',
                        'auth_type' => 'machine-to-machine',
                    ]
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'SSO M2M Login gagal',
                'errors' => $data,
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('[SSO M2M] Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghubungi SSO server: ' . $e->getMessage(),
                'errors' => null,
            ], 502);
        }
    }

    /**
     * Menampilkan user info dari JWT yang sudah diverifikasi + role lokal.
     * 
     * Endpoint ini dilindungi oleh middleware iae.sso.
     * GET /api/v1/sso/me
     */
    public function me(Request $request)
    {
        $ssoUser = $request->input('sso_user', []);
        $email = $request->input('sso_email');
        $localRole = $request->input('sso_local_role');

        // Ambil data role lengkap dari database
        $roleRecord = $email ? Role::where('email', $email)->first() : null;

        return response()->json([
            'status' => 'success',
            'message' => 'SSO User info retrieved successfully',
            'data' => [
                'sso_payload' => $ssoUser,
                'email' => $email,
                'local_role' => $localRole,
                'role_details' => $roleRecord,
            ],
            'meta' => [
                'service_name' => 'Lahan-Lokasi-Service',
                'api_version' => 'v1',
                'auth_source' => 'iae-sso-jwt',
            ]
        ], 200);
    }

    /**
     * Pastikan user yang login via SSO memiliki record di tabel roles lokal.
     * Jika belum ada, buat dengan role default 'viewer'.
     */
    protected function ensureLocalRole(string $email): void
    {
        Role::firstOrCreate(
            ['email' => $email],
            ['role' => 'viewer', 'name' => $email]
        );
    }
}
