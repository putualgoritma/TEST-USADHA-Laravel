<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'code',
        'accounts_group_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function scopeFilterDates($query)
    {
        $date = explode(" - ", request()->input('from-to', "")); 

        if(count($date) != 2)
        {
            //$date = [now()->subDays(29)->format("Y-m-d"), now()->format("Y-m-d")];
            //$date = [now()->subDays(29)->format("Y-m-d"), now()->addDays(1)->format("Y-m-d")];
            $date = ["2020-01-01", now()->addDays(1)->format("Y-m-d")];
        }

        return $query->whereBetween('created_at', $date);
    }
}
