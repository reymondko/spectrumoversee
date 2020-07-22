<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderNotes extends Model
{
    protected $table = 'order_notes';
    protected $primaryKey = 'id';

    public function user()
    {
        return $this->hasOne('App\User','id','users_id');
    }
}
