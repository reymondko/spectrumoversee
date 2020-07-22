<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryStatusLogs extends Model
{
    protected $table = 'inventory_status_logs';
    protected $primaryKey = 'id';

    public function user()
    {
        return $this->hasOne('App\User','id','users_id');
    }
}
