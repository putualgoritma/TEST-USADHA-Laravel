<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    public $table = 'orders';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'created_at',
        'updated_at',
        'deleted_at',
        'code',
        'memo',
        'register',
        'total',
        'type',
        'status',
        'ledgers_id',
        'customers_id',
        'payment_type',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product', 'order_id', 'product_id')->withPivot([
            'quantity',
            'price',
        ]);
    }
    public function productdetails()
    {
        return $this->belongsToMany(Product::class, 'product_order_details', 'orders_id', 'products_id')->withPivot([
            'quantity',
            'type',
            'status',
            'warehouses_id',
        ]);
    }
}
