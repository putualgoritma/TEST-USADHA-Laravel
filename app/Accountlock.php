<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accountlock extends Model
{
    //use SoftDeletes;

    protected $fillable = [
        'account_id',
        'code',
    ];   
    
    public function accounts()
    {
        return $this->belongsTo(Account::class, 'account_id')->select('id', 'name');
    }
}
