<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    //public $table = 'orders';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'created_at',
        'updated_at',
        'deleted_at',
        'register',
        'title',
        'memo',
        'status',
    ];

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'ledger_entries', 'ledgers_id', 'accounts_id')->withPivot([
            'entry_type',
            'amount',
        ]);
    }
}
