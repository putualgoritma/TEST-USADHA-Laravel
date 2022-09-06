<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class PayreceivableTrs extends Model
{
    //use SoftDeletes;

    protected $table = 'payreceivables_trs';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'code',
        'payreceivable_id',
        'label',
        'memo',
        'register',
        'amount',
        'ledger_id',
        'account_id',
        'type',
        'status',
    ];

    public function payreceivables()
    {
        return $this->belongsTo(Payreceivable::class, 'payreceivable_id');
    }
    
}
