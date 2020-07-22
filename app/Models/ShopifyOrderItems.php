<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyOrderItems extends Model
{
    protected $table = 'shopify_integration_order_items';
    protected $primaryKey = 'id';

    public function batches()
    {
        return $this->belongsTo('App\ShopifyIntegrationOrders');
    }
}
