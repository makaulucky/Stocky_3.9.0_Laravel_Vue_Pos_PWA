<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transfer_details', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->integer('id', true);
			$table->integer('transfer_id')->index('transfer_id');
			$table->integer('product_id')->index('product_id_transfers');
			$table->integer('product_variant_id')->nullable()->index('product_variant_id_transfer');
			$table->float('cost', 10, 0);
			$table->float('TaxNet', 10, 0)->nullable();
			$table->string('tax_method', 192)->nullable()->default('1');
			$table->float('discount', 10, 0)->nullable();
			$table->string('discount_method', 192)->nullable()->default('1');
			$table->float('quantity', 10, 0);
			$table->float('total', 10, 0);
			$table->timestamps(6);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('transfer_details');
	}

}
