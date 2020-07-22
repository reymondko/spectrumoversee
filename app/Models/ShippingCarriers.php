<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingCarriers extends Model
{
    protected $table = 'shipping_carriers';
    protected $primaryKey = 'id';

    public function methods(){
        return $this->hasMany('App\Models\ShippingCarrierMethods','shipping_carriers_id','id');
    }
}
