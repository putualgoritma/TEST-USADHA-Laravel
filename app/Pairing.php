<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Pairing extends Model
{
    protected $fillable = [
        'bv_amount',
        'order_id',
        'register',
        'ref2_id',
        'ref1_id',
        'ref1_amount',
        'ref2_amount',
    ];
}
