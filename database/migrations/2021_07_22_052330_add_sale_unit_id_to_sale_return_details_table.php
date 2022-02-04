<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleUnitIdToSaleReturnDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_return_details', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('sale_unit_id')->nullable()->after('price')->index('sale_unit_id_return_details');
            $table->foreign('sale_unit_id', 'sale_unit_id_return_details')->references('id')->on('units')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_return_details', function (Blueprint $table) {
            $table->dropForeign('sale_unit_id_return_details');
        });
    }
}
