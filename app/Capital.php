<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Capital extends Model
{
    //use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'code',
        'customer_id',
        'description',
        'register',
        'amount',
        'account_debit',
        'account_credit',
        'ledger_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
