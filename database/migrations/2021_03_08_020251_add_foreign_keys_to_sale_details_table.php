<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToSaleDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('sale_details', function(Blueprint $table)
		{
			$table->foreign('sale_id', 'Details_Sale_id')->references('id')->on('sales')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('product_id', 'sale_product_id')->references('id')->on('products')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('product_variant_id', 'sale_product_variant_id')->references('id')->on('product_variants')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('sale_details', function(Blueprint $table)
		{
			$table->dropForeign('Details_Sale_id');
			$table->dropForeign('sale_product_id');
			$table->dropForeign('sale_product_variant_id');
		});
	}

}
