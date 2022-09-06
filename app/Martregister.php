<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Martregister extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'status',
        'password',
        'name',
        'phone',
        'email',
        'address',
        'referal_id',
    ];

    public function customers( )
    {
        return $this->belongsTo(Customer::class, 'customer_id')->select('*');
    }
}
