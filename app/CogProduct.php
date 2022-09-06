<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CogProduct extends Model
{
    protected $table = 'cogs_products';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'amount',
    ];
}
