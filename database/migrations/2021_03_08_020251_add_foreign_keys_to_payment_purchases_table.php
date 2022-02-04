<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPaymentPurchasesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('payment_purchases', function(Blueprint $table)
		{
			$table->foreign('purchase_id', 'factures_purchase_id')->references('id')->on('purchases')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('user_id', 'user_id_factures_achat')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('payment_purchases', function(Blueprint $table)
		{
			$table->dropForeign('factures_purchase_id');
			$table->dropForeign('user_id_factures_achat');
		});
	}

}
