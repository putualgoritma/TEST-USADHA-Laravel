<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PairingInfo extends Model
{
    use SoftDeletes;

    protected $table = 'pairing_info';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'order_id',
        'ref_id',
        'bv_total',
        'bvcv_amount',
        'ref1_fee_point_sale',
        'ref1_fee_point_upgrade',
        'ref2_fee_point_sale',
        'ref2_fee_point_upgrade',
        'ref1_flush_out',
        'ledger_id',
        'cba2',
        'cbmart',
        'points_fee_id',
        'points_upg_id',
        'ref2_id',
        'memo',
        'member_get_flush_out',
        'package_type',
        'ref_fee_lev',
        'customer_id',
    ];
}
