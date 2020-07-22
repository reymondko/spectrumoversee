<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locations extends Model
{
    protected $table = 'locations';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];

    public function inventory(){
        return $this->hasMany('App\Models\Inventory','last_scan_location','name');
    }
}
