<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'address',
        'type',
        'parking_type',
        'total_spots',
        'available_spots',
        'base_rate',
    ];
}
