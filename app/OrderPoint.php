<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderPoint extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'amount',
        'orders_id',
    ];

    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customers_id')->select('id', 'code', 'name');
    }

    public function orders()
    {
        return $this->belongsTo(Order::class, 'orders_id')->select('id', 'register');
    }

    public function scopeFilterInput($query)
    {
        if(request()->input('customer')!=""){
            $customer = request()->input('customer'); 

            return $query->where('customers_id', $customer);
        }else{
            return ;
        }
    }
    public function scopeFilterInputJoin($query)
    {
        if(request()->input('customer')!=""){
            $customer = request()->input('customer'); 

            return $query->where('order_points.customers_id', $customer);
        }else{
            return ;
        }
    }

    public function scopeFilterPointJoin($query)
    {
        if(request()->input('point')!=""){
            $point = request()->input('point'); 

            return $query->where('order_points.points_id', $point);
        }else{
            return ;
        }
    }
}
