<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyIntegrationOrders extends Model
{
    protected $table = 'shopify_integration_orders';
    protected $primaryKey = 'id';

    public function batches()
    {
        return $this->belongsTo('App\ShopifyIntegrations');
    }
}
