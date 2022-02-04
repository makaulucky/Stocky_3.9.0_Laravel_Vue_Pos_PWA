<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToProductWarehouseTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('product_warehouse', function(Blueprint $table)
		{
			$table->foreign('product_id', 'art_id')->references('id')->on('products')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('warehouse_id', 'mag_id')->references('id')->on('warehouses')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('product_variant_id', 'product_variant_id')->references('id')->on('product_variants')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('product_warehouse', function(Blueprint $table)
		{
			$table->dropForeign('art_id');
			$table->dropForeign('mag_id');
			$table->dropForeign('product_variant_id');
		});
	}

}
