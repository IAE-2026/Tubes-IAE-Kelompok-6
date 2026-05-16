<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MembershipController extends Controller
{
    private const DISCOUNT_MAP = [
        'perunggu' => 10,
        'perak' => 15,
        'emas' => 20,
        'platina' => 50,
    ];

    #[OA\Get(
        path: '/api/v1/memberships',
        summary: 'Melihat daftar seluruh member',
        security: [['ApiKeyAuth' => []]],
        tags: ['Keanggotaan'],
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil diambil'),
            new OA\Response(response: 401, description: 'Tidak terotorisasi'),
            new OA\Response(response: 403, description: 'Akses ditolak'),
        ]
    )]
    public function index(): JsonResponse
    {
        $memberships = Membership::all();

        return $this->successResponse('Data berhasil diambil', $memberships, [
            'total' => $memberships->count(),
        ]);
    }

    #[OA\Get(
        path: '/api/v1/memberships/{id}',
        summary: 'Mengecek detail dan status aktif seorang member',
        security: [['ApiKeyAuth' => []]],
        tags: ['Keanggotaan'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Member code (contoh: MEM001)', schema: new OA\Schema(type: 'string', example: 'MEM001')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil diambil'),
            new OA\Response(response: 404, description: 'Anggota tidak ditemukan'),
            new OA\Response(response: 401, description: 'Tidak terotorisasi'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $membership = Membership::where('member_code', $id)->first();

        if (!$membership) {
            return $this->errorResponse("Anggota '{$id}' tidak ditemukan", 404);
        }

        return $this->successResponse('Data berhasil diambil', $membership);
    }

    #[OA\Post(
        path: '/api/v1/memberships',
        summary: 'Mendaftarkan member baru',
        security: [['ApiKeyAuth' => []]],
        tags: ['Keanggotaan'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'phone', 'membership_type'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Rani Putri'),
                    new OA\Property(property: 'email', type: 'string', example: 'rani@mail.com'),
                    new OA\Property(property: 'phone', type: 'string', example: '081234567899'),
                    new OA\Property(property: 'membership_type', type: 'string', enum: ['perunggu', 'perak', 'emas', 'platina'], example: 'perak'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Anggota berhasil didaftarkan'),
            new OA\Response(response: 400, description: 'Validasi gagal'),
            new OA\Response(response: 401, description: 'Tidak terotorisasi'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $membershipType = $request->input('membership_type');

        if (!$name || !$email || !$phone || !$membershipType) {
            return $this->errorResponse('name, email, phone, dan membership_type wajib diisi', 400);
        }

        $validTypes = array_keys(self::DISCOUNT_MAP);
        if (!in_array($membershipType, $validTypes, true)) {
            return $this->errorResponse(
                'membership_type harus salah satu dari: ' . implode(', ', $validTypes),
                400
            );
        }

        $lastMembership = Membership::orderByDesc('id')->first();
        $nextNumber = $lastMembership ? ((int) substr($lastMembership->member_code, 3)) + 1 : 1;
        $memberCode = 'MEM' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $now = now();
        $expiredAt = $now->copy()->addYear();

        $membership = Membership::create([
            'member_code' => $memberCode,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'membership_type' => $membershipType,
            'status' => 'aktif',
            'discount_percent' => self::DISCOUNT_MAP[$membershipType],
            'registered_at' => $now,
            'expired_at' => $expiredAt,
        ]);

        return $this->successResponse('Anggota berhasil didaftarkan', $membership, [], 201);
    }

    private function successResponse(string $message, mixed $data, array $extraMeta = [], int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'meta' => array_merge([
                'service_name' => 'Keanggotaan-Voucher-Service',
                'api_version' => 'v1',
            ], $extraMeta),
        ], $statusCode);
    }

    private function errorResponse(string $message, int $statusCode, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
