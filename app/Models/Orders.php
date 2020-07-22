<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';

    public function orderItems()
    {
        return $this->hasMany('App\Models\OrderItems','orders_id','id');
    }

    public function orderNotes()
    {
        return $this->hasMany('App\Models\OrderNotes','orders_id','id');
    }

    
}
