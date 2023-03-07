<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    //use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'parent_id',
        'ref_id',
        'register',
        'code',
        'password',
        'name',
        'last_name',
        'phone',
        'phone2',
        'email',
        'address',
        'address2',
        'type',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
        'description',
        'activation_type_id',
        'activation_at',
        'province_id',
        'city_id',
        'customer_agent_id',
        'agent_type',
        'ref_bin_id',
    ];

    public function activations()
    {
        return $this->belongsTo(Activation::class, 'activation_type_id')->select('id', 'name');
    }

    public function refferal()
    {
        return $this->belongsTo(CustomerApi::class, 'ref_bin_id')->select('id', 'code', 'name');
    }

        
    public function provinces( )
    {
        return $this->belongsTo(Province::class, 'province_id')->select('id', 'title');
    }

    public function city( )
    {
        return $this->belongsTo(City::class, 'city_id')->select('id', 'title');
    }
    
    public function scopeFilterInput($query)
    {
        if(request()->input('status')!=""){
            $status = request()->input('status', "active"); 

            return $query->where('customers.status', $status);
        }else{
            return $query->where('customers.status', 'active')
            ->orWhere('customers.status', '=', 'pending');
        }
    }
}
