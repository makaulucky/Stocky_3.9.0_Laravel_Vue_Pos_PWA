<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseUnitIdToPurchaseReturnDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_return_details', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('purchase_unit_id')->nullable()->after('cost')->index('unit_id_purchase_return_details');
            $table->foreign('purchase_unit_id', 'unit_id_purchase_return_details')->references('id')->on('units')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_return_details', function (Blueprint $table) {
            $table->dropForeign('unit_id_purchase_return_details');
        });
    }
}
