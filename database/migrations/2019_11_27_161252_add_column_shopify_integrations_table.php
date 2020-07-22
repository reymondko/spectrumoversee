<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnShopifyIntegrationsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shopify_integrations')) {
            Schema::table('shopify_integrations', function (Blueprint $table) {
                $table->integer('tpl_customer_id')->after('companies_id')->nullable();;
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('shopify_integrations')) {
            Schema::table('shopify_integrations', function (Blueprint $table) {
                $table->dropColumn('tpl_customer_id');
            });
        }
    }
}
