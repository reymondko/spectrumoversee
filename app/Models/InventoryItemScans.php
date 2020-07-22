<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItemScans extends Model
{
    protected $table = 'inventory_item_scans';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->hasOne('App\User','id','scanned_by_user_id');
    }

    public function inventory(){
        return $this->hasOne('App\Models\Inventory','id','inventory_item_id');
    }
}
