<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Services\SoapAuditService;
use App\Services\AmqpPublisherService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class LocationController extends Controller {
    
    #[OA\Get(
        path: "/locations",
        operationId: "getLocationsList",
        tags: ["Locations"],
        summary: "Melihat daftar lokasi parkir",
        security: [["ApiKeyAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function index() {
        $locations = Location::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $locations,
            'meta' => [
                'service_name' => 'Lahan-Lokasi-Service',
                'api_version' => 'v1'
            ]
        ], 200);
    }

    #[OA\Get(
        path: "/locations/{id}",
        operationId: "getLocationById",
        tags: ["Locations"],
        summary: "Melihat detail satu lokasi",
        security: [["ApiKeyAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Resource not found")
        ]
    )]
    public function show($id) {
        $location = Location::find($id);
        if (!$location) {
            return response()->json([
                'status' => 'error',
                'message' => "Resource with ID $id not found",
                'errors' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $location,
            'meta' => [
                'service_name' => 'Lahan-Lokasi-Service',
                'api_version' => 'v1'
            ]
        ], 200);
    }

    #[OA\Post(
        path: "/locations",
        operationId: "storeLocation",
        tags: ["Locations"],
        summary: "Menambahkan master data lahan baru (triggers SOAP Audit & AMQP Event)",
        security: [["ApiKeyAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "address", "type", "parking_type", "total_spots", "base_rate"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Gedung Kuliah Umum Parkir"),
                    new OA\Property(property: "address", type: "string", example: "Jl. Telekomunikasi No. 1, Bandung"),
                    new OA\Property(property: "type", type: "string", example: "indoor"),
                    new OA\Property(property: "parking_type", type: "string", example: "regular"),
                    new OA\Property(property: "total_spots", type: "integer", example: 100),
                    new OA\Property(property: "base_rate", type: "integer", example: 3000)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Resource created successfully"),
            new OA\Response(response: 400, description: "Validation Error")
        ]
    )]
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'address' => 'required|string',
            'type' => 'required|string',
            'parking_type' => 'required|string',
            'total_spots' => 'required|integer',
            'base_rate' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()->all()
            ], 400);
        }

        $latestId = Location::orderBy('id', 'desc')->first();
        $num = $latestId ? ((int) substr($latestId->id, 4)) + 1 : 1;
        $customId = 'loc_' . str_pad($num, 3, '0', STR_PAD_LEFT);

        $location = new Location();
        $location->id = $customId;
        $location->name = $request->name;
        $location->address = $request->address;
        $location->type = $request->type;
        $location->parking_type = $request->parking_type;
        $location->total_spots = $request->total_spots;
        $location->available_spots = $request->total_spots; 
        $location->base_rate = $request->base_rate;
        $location->save();

        // Integrasi SOAP Audit & AMQP Publisher
        $integrationResults = ['soap_audit' => null, 'amqp_publish' => null];
        $bearerToken = $this->obtainBearerToken($request);

        if ($bearerToken) {
            // SOAP Audit
            try {
                $soapService = new SoapAuditService();
                $auditResult = $soapService->sendAudit(
                    'LocationCreated',
                    [
                        'location_id' => $location->id,
                        'name' => $location->name,
                        'address' => $location->address,
                        'type' => $location->type,
                        'parking_type' => $location->parking_type,
                        'total_spots' => $location->total_spots,
                        'base_rate' => $location->base_rate,
                        'created_at' => $location->created_at?->toIso8601String(),
                    ],
                    $location->id,
                    $bearerToken
                );
                $integrationResults['soap_audit'] = $auditResult;
            } catch (\Exception $e) {
                Log::error('[Store] SOAP error', ['error' => $e->getMessage()]);
                $integrationResults['soap_audit'] = ['success' => false, 'error' => $e->getMessage()];
            }

            // AMQP Publisher
            try {
                $receiptNumber = $integrationResults['soap_audit']['receipt_number'] ?? null;

                $amqpService = new AmqpPublisherService();
                $publishResult = $amqpService->publish(
                    'location.created',
                    [
                        'location_id' => $location->id,
                        'name' => $location->name,
                        'address' => $location->address,
                        'type' => $location->type,
                        'parking_type' => $location->parking_type,
                        'total_spots' => $location->total_spots,
                        'available_spots' => $location->available_spots,
                        'base_rate' => $location->base_rate,
                        'receipt_number' => $receiptNumber,
                        'created_at' => $location->created_at?->toIso8601String(),
                        'updated_at' => $location->updated_at?->toIso8601String(),
                    ],
                    $bearerToken
                );
                $integrationResults['amqp_publish'] = $publishResult;
            } catch (\Exception $e) {
                Log::error('[Store] AMQP error', ['error' => $e->getMessage()]);
                $integrationResults['amqp_publish'] = ['success' => false, 'error' => $e->getMessage()];
            }
        } else {
            Log::warning('[Store] No bearer token available');
            $integrationResults['soap_audit'] = ['success' => false, 'error' => 'No bearer token'];
            $integrationResults['amqp_publish'] = ['success' => false, 'error' => 'No bearer token'];
        }

        $receiptNumber = $integrationResults['soap_audit']['receipt_number'] ?? null;
        $locationData = $location->toArray();
        if ($receiptNumber) {
            $locationData['receipt_number'] = $receiptNumber;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Location master data created successfully',
            'data' => [
                'location' => $locationData,
                'receipt_number' => $receiptNumber,
            ],
            'meta' => [
                'service_name' => 'Lahan-Lokasi-Service',
                'api_version' => 'v1'
            ]
        ], 201);
    }

    /**
     * M2M token untuk integrasi Cloud Pusat (agar pengirim = TEAM-06).
     */
    protected function obtainBearerToken(Request $request): ?string
    {
        try {
            $ssoUrl = rtrim(env('IAE_SSO_URL', 'https://iae-sso.virtualfri.id'), '/');
            $apiKey = env('IAE_API_KEY', 'KEY-MHS-67');
            $nim = env('IAE_NIM', '102022400039');

            $response = Http::timeout(15)->post("{$ssoUrl}/api/v1/auth/token", [
            'api_key' => $apiKey,
            'nim' => $nim,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['token'] ?? $data['access_token'] ?? $data['data']['token'] ?? null;
                if ($token) return $token;
            }
        } catch (\Exception $e) {
            Log::error('[Auth] M2M token failed', ['error' => $e->getMessage()]);
        }

        // Fallback: SSO token dari request
        $ssoToken = $request->input('sso_token');
        if ($ssoToken) return $ssoToken;

        return null;
    }

    #[OA\Post(
        path: "/locations/{id}/occupy",
        operationId: "occupyLocationSpot",
        tags: ["Locations"],
        summary: "Mengurangi slot parkir yang tersedia saat digunakan",
        security: [["ApiKeyAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string")),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "slots", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Spot occupied successfully"),
            new OA\Response(response: 400, description: "Validation error or insufficient slots"),
            new OA\Response(response: 404, description: "Location not found")
        ]
    )]
    public function occupy(Request $request, $id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json([
                'status' => 'error',
                'message' => "Resource with ID $id not found",
                'errors' => null
            ], 404);
        }

        $slots = $request->input('slots', 1);
        if (!is_numeric($slots) || (int) $slots <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => ['The slots field must be a positive integer.']
            ], 400);
        }
        $slots = (int) $slots;

        if ($location->available_spots < $slots) {
            return response()->json([
                'status' => 'error',
                'message' => "Insufficient available spots. Remaining spots: $location->available_spots, requested: $slots",
                'errors' => null
            ], 400);
        }

        $location->available_spots -= $slots;
        $location->save();

        return response()->json([
            'status' => 'success',
            'message' => "$slots parking spot(s) successfully occupied",
            'data' => $location,
            'meta' => [
                'service_name' => 'Lahan-Lokasi-Service',
                'api_version' => 'v1'
            ]
        ], 200);
    }

    #[OA\Post(
        path: "/locations/{id}/release",
        operationId: "releaseLocationSpot",
        tags: ["Locations"],
        summary: "Menambah slot parkir yang tersedia saat slot kembali kosong",
        security: [["ApiKeyAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string")),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "slots", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Spot released successfully"),
            new OA\Response(response: 400, description: "Validation error or capacity exceeded"),
            new OA\Response(response: 404, description: "Location not found")
        ]
    )]
    public function release(Request $request, $id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json([
                'status' => 'error',
                'message' => "Resource with ID $id not found",
                'errors' => null
            ], 404);
        }

        $slots = $request->input('slots', 1);
        if (!is_numeric($slots) || (int) $slots <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => ['The slots field must be a positive integer.']
            ], 400);
        }
        $slots = (int) $slots;

        if ($location->available_spots + $slots > $location->total_spots) {
            return response()->json([
                'status' => 'error',
                'message' => "Cannot release spots beyond total capacity. Total capacity: $location->total_spots, current available: $location->available_spots, requested release: $slots",
                'errors' => null
            ], 400);
        }

        $location->available_spots += $slots;
        $location->save();

        return response()->json([
            'status' => 'success',
            'message' => "$slots parking spot(s) successfully released",
            'data' => $location,
            'meta' => [
                'service_name' => 'Lahan-Lokasi-Service',
                'api_version' => 'v1'
            ]
        ], 200);
    }

    #[OA\Post(
        path: "/events/rabbitmq-callback",
        operationId: "handleRabbitMQEventCallback",
        tags: ["Locations"],
        summary: "Simulasi penerimaan event RabbitMQ (webhook) untuk slot management",
        security: [["ApiKeyAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["event", "data"],
                properties: [
                    new OA\Property(property: "event", type: "string", example: "parking.slot.occupied"),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "location_id", type: "string", example: "loc_001"),
                        new OA\Property(property: "slots", type: "integer", example: 1)
                    ])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Event processed successfully"),
            new OA\Response(response: 400, description: "Validation error or process failure"),
            new OA\Response(response: 404, description: "Location not found")
        ]
    )]
    public function handleEventCallback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event' => 'required|string|in:parking.slot.occupied,parking.slot.released,parking.payment.completed',
            'data.location_id' => 'required|string',
            'data.slots' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()->all()
            ], 400);
        }

        $event = $request->input('event');
        $locationId = $request->input('data.location_id');
        $slots = (int) $request->input('data.slots', 1);

        $location = Location::find($locationId);
        if (!$location) {
            return response()->json([
                'status' => 'error',
                'message' => "Location with ID $locationId not found",
                'errors' => null
            ], 404);
        }

        if ($event === 'parking.slot.occupied') {
            if ($location->available_spots < $slots) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Insufficient spots. Remaining spots: $location->available_spots, requested: $slots",
                    'errors' => null
                ], 400);
            }
            $location->available_spots -= $slots;
        } elseif ($event === 'parking.slot.released' || $event === 'parking.payment.completed') {
            if ($location->available_spots + $slots > $location->total_spots) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Releasing spots exceeds total spots capacity. Total capacity: $location->total_spots, current: $location->available_spots, requested: $slots",
                    'errors' => null
                ], 400);
            }
            $location->available_spots += $slots;
        }

        $location->save();

        Log::info('[RabbitMQ Webhook] Event simulation processed', [
            'event' => $event,
            'location_id' => $locationId,
            'slots' => $slots,
            'new_available_spots' => $location->available_spots
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Event $event successfully processed",
            'data' => [
                'event' => $event,
                'location' => $location,
            ],
            'meta' => [
                'service_name' => 'Lahan-Lokasi-Service',
                'api_version' => 'v1'
            ]
        ], 200);
    }
}
