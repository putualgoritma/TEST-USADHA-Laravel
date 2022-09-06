<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activation extends Model
{
    protected $table = 'activation_type'; 

    protected $fillable = [        
        'code',
        'name',   
    ];
}
