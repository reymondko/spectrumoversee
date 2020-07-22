<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseLabelRequiredFields extends Model
{
    protected $table = 'caselabel_required_fields';
    protected $primaryKey = 'id';

    public function inventoryField(){
        return $this->hasOne('App\Models\InventoryFields','id','inventory_fields_id')->latest();
    }
}
