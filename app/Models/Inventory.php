<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];

    public function latestScan(){
        return $this->hasOne('App\Models\InventoryItemScans','inventory_item_id','id')->latest();
    }

    public function latestScanMax(){
        return $this->hasOne('App\Models\InventoryItemScans','inventory_item_id','id')->orderBy('id','desc')->limit(1);
    }

    public function inventoryNotes()
    {
        return $this->hasMany('App\Models\InventoryNotes','inventory_id','id');
    }

    public function inventoryStatusLogs()
    {
        return $this->hasMany('App\Models\InventoryStatusLogs','inventory_id','id');
    }
}
