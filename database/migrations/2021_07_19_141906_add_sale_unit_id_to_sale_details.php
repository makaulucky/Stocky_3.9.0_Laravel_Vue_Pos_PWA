<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleUnitIdToSaleDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('sale_unit_id')->nullable()->after('price')->index('sales_sale_unit_id');
            $table->foreign('sale_unit_id', 'sales_sale_unit_id')->references('id')->on('units')->onUpdate('RESTRICT')->onDelete('RESTRICT');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropForeign('sales_sale_unit_id');
        });
    }
}
