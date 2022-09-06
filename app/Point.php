<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'code',
        'name',
    ];

    public function scopeFilterWithdraw($query,$status_fld)
    {
        if(!empty($status_fld) && $status_fld!==''){
            return $query->where($status_fld, 1);
        }else{
            return ;
        }        
    }
}
