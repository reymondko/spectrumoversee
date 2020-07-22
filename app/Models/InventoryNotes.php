<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryNotes extends Model
{
    protected $table = 'inventory_notes';
    protected $primaryKey = 'id';

    public function user()
    {
        return $this->hasOne('App\User','id','users_id');
    }
}
