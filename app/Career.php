<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Career extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'customer_id',
        'careertype_id',
        'current_ro_amount',
        'status',
    ];   

    public function careertypes()
    {
        return $this->belongsTo(Careertype::class, 'careertype_id')->select('id', 'name');
    }
    
}
