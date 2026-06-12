<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditReceipt extends Model
{
    protected $fillable = [
        'transaction_type',
        'reference_id',
        'receipt_number',
        'status',
        'soap_request',
        'soap_response',
    ];
}
