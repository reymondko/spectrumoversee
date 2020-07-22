<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAutomationRules extends Model
{
    protected $table = 'shipping_automation_rules';
    protected $primaryKey = 'id';

    public function company(){
        return $this->hasOne('App\Models\Companies','id','companies_id');
    }
}
