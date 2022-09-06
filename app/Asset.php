<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Asset extends Model
{
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'code',
        'name',
        'description',
        'register',
        'qty',
        'depreciation_type',
        'value',
        'life_period',
        'ledger_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
