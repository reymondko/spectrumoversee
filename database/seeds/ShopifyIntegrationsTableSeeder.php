<?php

use Illuminate\Database\Seeder;
use App\Models\ShopifyIntegrations;

class ShopifyIntegrationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ShopifyIntegrations::create([
            'id' => 1,
            'companies_id' => '1',
            'shopify_url' => 'digiance-test-store.myshopify.com',
            'shopify_api_key' => 'cc4d84282bae353cb2ec650847d54f3c',
            'shopify_password' => 'f3c83d628110c69049d609ef2f7a9c30'
        ]);
    }
}
