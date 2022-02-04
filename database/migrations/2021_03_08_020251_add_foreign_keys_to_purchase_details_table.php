<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPurchaseDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('purchase_details', function(Blueprint $table)
		{
			$table->foreign('product_id', 'product_id')->references('id')->on('products')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('purchase_id', 'purchase_id')->references('id')->on('purchases')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('product_variant_id', 'purchase_product_variant_id')->references('id')->on('product_variants')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('purchase_details', function(Blueprint $table)
		{
			$table->dropForeign('product_id');
			$table->dropForeign('purchase_id');
			$table->dropForeign('purchase_product_variant_id');
		});
	}

}
