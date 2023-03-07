<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class NetworkFee extends Model
{
    use SoftDeletes;

    protected $table = 'network_fees'; 

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [        
        'code',
        'name',
        'amount',
        'type',
        'deep_level',
        'fee_day_max',
        'activation_type_id',
        'sbv',
        'saving',
        'rsbv_g1',
        'rsbv_g2',
        'cba2',
        'created_at',
        'updated_at',
        'deleted_at',
        'bv_min_pairing',
        'sbv2',      
    ];
}
