<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Careertype extends Model
{
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'activation_type_id',
        'ro_min_bv',
        'fee_min',
        'fee_max',
        'ref_downline_num',
        'ref_downline_id',
        'team_level',
        'auto_maintain_bv',
        'fee_am',
    ];
    
    public function activations()
    {
        return $this->belongsTo(Activation::class, 'activation_type_id')->select('id', 'name');
    }

    public function activationdownlines()
    {
        return $this->belongsTo(Activation::class, 'ref_downline_id')->select('id', 'name');
    }

    public function careertypes()
    {
        return $this->belongsToMany(Careertype::class, 'careertypelevels', 'careertype_id', 'careertype_level_id')
        ->withPivot([
            'amount'
        ]);
    }

    public function activationtypes()
    {
        return $this->belongsToMany(Activation::class, 'careertypeactivations', 'careertype_id', 'activation_id')
        ->withPivot([
            'amount'
        ]);
    }
}
