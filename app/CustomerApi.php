<?php
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class CustomerApi extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'register',
        'code',
        'password',
        'name',
        'phone',
        'email',
        'address',
        'type',
        'status',
        'parent_id',
        'ref_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'status_block',
        'lat',
        'lng',
        'province_id',
        'city_id',
        'ref_bin_id',
        'slot_x',
        'slot_y',
        'owner_id',
        'activation_type_id'
    ];

    public function scopeFilterInput($query)
    {
        if(!empty(request()->input('keyword'))){
            $keyword = "%".request()->input('keyword')."%"; 
            return $query->where('name', 'LIKE', $keyword);
        }else{
            return ;
        }
    }

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
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
