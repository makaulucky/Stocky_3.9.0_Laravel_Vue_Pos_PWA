<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseUnitIdToPurchaseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('purchase_unit_id')->nullable()->after('cost')->index('purchase_unit_id_purchase');
            $table->foreign('purchase_unit_id', 'purchase_unit_id_purchase')->references('id')->on('units')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->dropForeign('purchase_unit_id_purchase');
        });
    }
}
