<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipUsage extends Model
{
    protected $fillable = [
        'membership_id',
        'transaction_id',
        'voucher_code',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
