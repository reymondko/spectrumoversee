<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationOrderSettings extends Model
{
    protected $table = 'notification_order_settings';
    protected $primaryKey = 'id';
    protected $fillable = array('companies_id');

}
