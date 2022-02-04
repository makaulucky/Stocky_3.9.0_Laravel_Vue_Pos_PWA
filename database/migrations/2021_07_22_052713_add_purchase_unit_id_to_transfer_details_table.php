<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseUnitIdToTransferDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfer_details', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('purchase_unit_id')->nullable()->after('cost')->index('unit_sale_id_transfer');
            $table->foreign('purchase_unit_id', 'unit_sale_id_transfer')->references('id')->on('units')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfer_details', function (Blueprint $table) {
            $table->dropForeign('unit_sale_id_transfer');
        });
    }
}
