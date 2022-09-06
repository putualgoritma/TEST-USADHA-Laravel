<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Payreceivable extends Model
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
        'label',
        'memo',
        'register',
        'type',
        'status',
        'amount',
        'account_id',
        'account_pay',
        'ledger_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function ledgers()
    {
        return $this->belongsToMany(Ledger::class, 'payreceivables_trs', 'payreceivable_id', 'ledger_id')->withPivot([
            'code',
            'memo',
            'register',
            'amount',
            'account_id',
            'type',
            'status',
        ]);
    }

    public function scopeFilterInput($query)
    {
        if(request()->input('customer')!=""){
            $customer = request()->input('customer'); 

            return $query->where('payreceivables.customer_id', $customer);
        }else{
            return ;
        }
    }
}
