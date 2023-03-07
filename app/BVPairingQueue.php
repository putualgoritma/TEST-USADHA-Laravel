<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BVPairingQueue extends Model
{
    protected $table = 'bv_pairing_queue';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'order_id',
        'customer_id',
        'bv_amount',
        'position',
        'status',
        'type',
        'created_at',
        'updated_at',
        'deleted_at',
        'pairing_amount',
    ];
    
}
