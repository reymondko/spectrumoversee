<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperAddress extends Model
{
    protected $table = 'shipper_address';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
}
