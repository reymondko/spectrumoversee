<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableShippingCarrierMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('shipping_carrier_methods')) {
          Schema::table('shipping_carrier_methods', function (Blueprint $table) {
              $table->integer('shipping_carriers_id')->change();
              $table->decimal('markup', 6, 2)->default(0.00)->after('value');
              $table->integer('shipping_vendor_id')->default(0)->after('shipping_carriers_id');
              $table->string('account_number')->nullable()->after('value');
          });
      }

      Schema::create('shipping_vendors', function (Blueprint $table) {
          $table->increments('id');
          $table->text('vendor_name')->nullable();
          $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_vendors');
    }
}
