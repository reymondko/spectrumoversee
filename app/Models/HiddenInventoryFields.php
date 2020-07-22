<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HiddenInventoryFields extends Model
{
    protected $table = 'hidden_inventory_fields';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
