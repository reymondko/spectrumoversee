<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function my_role()
    {
        return $this->hasOne('App\Models\Roles', 'id', 'role');
    }

    public function companies()
    {
        return $this->hasOne('App\Models\Companies','id','companies_id');
    }

    public function locations()
    {
        return $this->hasOne('App\Models\Locations','id','location_id');
    }

    public function permissions(){
        return $this->hasMany('App\Models\UserPermissions','users_id','id');
    }

    public function logs(){
        return $this->hasMany('App\Models\UserLogs','users_id','id');
    }

    public function latestLogIn(){
        return $this->hasOne('App\Models\LastLogin','users_id','id');
    }
}
