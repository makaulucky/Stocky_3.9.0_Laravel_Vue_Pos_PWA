<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('products', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->integer('id', true);
			$table->string('code', 192);
			$table->string('Type_barcode', 192);
			$table->string('name', 192);
			$table->float('cost', 10, 0);
			$table->float('price', 10, 0);
			$table->integer('category_id')->index('category_id');
			$table->integer('brand_id')->nullable()->index('brand_id_products');
			$table->integer('unit_id')->nullable()->index('unit_id_products');
			$table->integer('unit_sale_id')->nullable()->index('unit_id_sales');
			$table->integer('unit_purchase_id')->nullable()->index('unit_purchase_products');
			$table->float('TaxNet', 10, 0)->nullable()->default(0);
			$table->string('tax_method', 192)->nullable()->default('1');
			$table->text('image')->nullable();
			$table->text('note')->nullable();
			$table->float('stock_alert', 10, 0)->nullable()->default(0);
			$table->boolean('is_variant')->default(0);
			$table->boolean('is_active')->nullable()->default(1);
			$table->timestamps(6);
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('products');
	}

}
