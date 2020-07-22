<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    protected $table = 'order_items';
    protected $primaryKey = 'id';

    public function inventories()
    {
        return $this->hasMany('App\Models\Inventory','order_item_id','id');
    }

}
