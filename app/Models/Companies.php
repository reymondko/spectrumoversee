<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';


    public function LogiwaDepositor()
    {
        return $this->hasMany('App\Models\LogiwaDepositor','companies_id','id');
    }

}
