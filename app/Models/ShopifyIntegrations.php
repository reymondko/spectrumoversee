<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyIntegrations extends Model
{
    protected $table = 'shopify_integrations';
    protected $primaryKey = 'id';

    public function ignoredSkus(){
        return $this->hasOne('App\Models\ShopifyIgnoredSku','shopify_integration_id','id');
    }

}
