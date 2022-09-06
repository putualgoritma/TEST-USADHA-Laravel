<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivationType extends Model
{
    //use SoftDeletes;
    protected $table = 'activation_type';

    protected $fillable = [
        'name',
        'type',
        'bv_min',
        'bv_max'
    ];    
}
