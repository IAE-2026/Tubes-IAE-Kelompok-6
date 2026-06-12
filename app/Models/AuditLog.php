<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Jejak audit setiap pengiriman SOAP ke sistem audit legacy dosen (Modul 2).
 * Menyimpan envelope yang dikirim, respons mentah, dan ReceiptNumber hasil parsing.
 */
class AuditLog extends Model
{
    protected $fillable = [
        'transaction_id',
        'activity_name',
        'team_id',
        'log_content',
        'soap_request',
        'soap_response',
        'receipt_number',
        'status',
        'http_status',
        'error_message',
    ];

    protected $casts = [
        'log_content' => 'array',
        'http_status' => 'integer',
    ];
}
