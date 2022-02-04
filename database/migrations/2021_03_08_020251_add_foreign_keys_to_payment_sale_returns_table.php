<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPaymentSaleReturnsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('payment_sale_returns', function(Blueprint $table)
		{
			$table->foreign('sale_return_id', 'factures_sale_return')->references('id')->on('sale_returns')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('user_id', 'factures_sale_return_user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('payment_sale_returns', function(Blueprint $table)
		{
			$table->dropForeign('factures_sale_return');
			$table->dropForeign('factures_sale_return_user_id');
		});
	}

}
