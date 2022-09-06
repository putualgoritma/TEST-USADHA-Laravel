<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountsGroup extends Model
{
    use SoftDeletes;    

    protected $table = 'accounts_group';    

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'code',
        'name',
        'accounts_type_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
