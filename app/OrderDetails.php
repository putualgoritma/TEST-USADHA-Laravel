<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    protected $table = 'product_order_details';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'quantity',
    ];

    public function orders()
    {
        return $this->belongsTo(Order::class, 'orders_id')->select('id', 'code', 'register', 'memo');
    }

    public function products()
    {
        return $this->belongsTo(Product::class, 'products_id')->select('id', 'name');
    }

    public function scopeFilterProduct($query)
    {
        if(!empty(request()->input('product'))){
            $product = request()->input('product'); 
            return $query->where('products_id', $product);
        }else{
            return ;
        }
    }

    public function scopeFilterProductJoin($query)
    {
        if(!empty(request()->input('product'))){
            $product = request()->input('product'); 
            return $query->where('product_order_details.products_id', $product);
        }else{
            return ;
        }
    }

}
