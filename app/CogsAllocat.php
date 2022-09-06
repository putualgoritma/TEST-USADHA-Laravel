<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CogsAllocat extends Model
{
    use SoftDeletes;    

    protected $table = 'cogs_allocats';    

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'account_id',
        'allocation',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
