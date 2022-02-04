<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sale_details', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->integer('id', true);
			$table->date('date');
			$table->integer('sale_id')->index('Details_Sale_id');
			$table->integer('product_id')->index('sale_product_id');
			$table->integer('product_variant_id')->nullable()->index('sale_product_variant_id');
			$table->float('price', 10, 0);
			$table->float('TaxNet', 10, 0)->nullable();
			$table->string('tax_method', 192)->nullable()->default('1');
			$table->float('discount', 10, 0)->nullable();
			$table->string('discount_method', 192)->nullable()->default('1');
			$table->float('total', 10, 0);
			$table->float('quantity', 10, 0);
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
		Schema::drop('sale_details');
	}

}
