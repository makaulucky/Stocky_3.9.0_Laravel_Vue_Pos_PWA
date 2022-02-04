<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTransferDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('transfer_details', function(Blueprint $table)
		{
			$table->foreign('product_id', 'product_id_transfers')->references('id')->on('products')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('product_variant_id', 'product_variant_id_transfer')->references('id')->on('product_variants')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('transfer_id', 'transfer_id')->references('id')->on('transfers')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('transfer_details', function(Blueprint $table)
		{
			$table->dropForeign('product_id_transfers');
			$table->dropForeign('product_variant_id_transfer');
			$table->dropForeign('transfer_id');
		});
	}

}
