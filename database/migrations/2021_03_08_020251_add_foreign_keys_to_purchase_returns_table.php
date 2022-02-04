<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPurchaseReturnsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('purchase_returns', function(Blueprint $table)
		{
			$table->foreign('provider_id', 'provider_id_return')->references('id')->on('providers')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('user_id', 'purchase_return_user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('warehouse_id', 'purchase_return_warehouse_id')->references('id')->on('warehouses')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('purchase_returns', function(Blueprint $table)
		{
			$table->dropForeign('provider_id_return');
			$table->dropForeign('purchase_return_user_id');
			$table->dropForeign('purchase_return_warehouse_id');
		});
	}

}
