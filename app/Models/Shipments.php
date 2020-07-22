<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipments extends Model
{
    protected $table = 'shipments';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];

    public function deliveryAddress()
    {
        return $this->hasOne('App\Models\DeliveryAddress','id','delivery_address_id');
    }

    public function shipperAddress()
    {
        return $this->hasOne('App\Models\ShipperAddress','id','shipper_address_id');
    }

    public function company()
    {
        return $this->hasOne('App\Models\Companies','id','companies_id');
    }
}
