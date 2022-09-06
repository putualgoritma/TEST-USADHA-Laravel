<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogNotif extends Model
{
    public $table = 'logs_notif';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'customers_id',
        'register',
        'memo',
    ];

    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customers_id');
    }
}
