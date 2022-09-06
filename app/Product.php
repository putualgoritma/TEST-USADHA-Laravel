<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Product extends Model
{
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'price',
        'cogs',
        'created_at',
        'updated_at',
        'deleted_at',
        'description',
        'img',
        'bv',
        'discount',
        'status',
        'profit',
    ];

    public function scopeFilterStatus($query)
    {
        if(!empty(request()->input('status'))){
            $status = request()->input('status'); 
            return $query->where('products.status', $status);
        }else{
            return $query->where('products.status', 'show');
        }
    }

    public function scopeFilterInput($query)
    {
        if(!empty(request()->input('keyword'))){
            $keyword = "%".request()->input('keyword')."%"; 
            return $query->where('name', 'LIKE', $keyword);
        }else{
            return ;
        }
    }
    
    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'cogs_products', 'products_id', 'accounts_id')
        ->withPivot([
            'amount'
        ]);
    }
    
    public function orderdetailsSum()
    {
        return $this->hasMany(OrderDetails::class, 'products_id')
        ->select('quantity')
        ->groupBy('products_id');
    }
}
