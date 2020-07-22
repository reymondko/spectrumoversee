<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipPackSubmissions extends Model
{
    protected $table = 'ship_pack_submissions';
    protected $primaryKey = 'id';

    public function tpl_client()
    {
        return $this->hasOne('App\Models\ShippingClient','tpl_client_id','tpl_customer_id');
    }

    public function fulffiller()
    {
        return $this->hasOne('App\User','id','user_id');
    }

    public function company()
    {
        return $this->hasOne('App\Models\Companies','id','companies_id');
    }

    public function vendor()
    {
        return $this->hasOne('App\Models\ShippingVendors','id','shipping_vendor_id');
    }

}