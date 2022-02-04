<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sales', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->integer('id', true);
			$table->integer('user_id')->index('user_id_sales');
			$table->date('date');
			$table->string('Ref', 192);
			$table->boolean('is_pos')->nullable()->default(0);
			$table->integer('client_id')->index('sale_client_id');
			$table->integer('warehouse_id')->index('warehouse_id_sale');
			$table->float('tax_rate', 10, 0)->nullable()->default(0);
			$table->float('TaxNet', 10, 0)->nullable()->default(0);
			$table->float('discount', 10, 0)->nullable()->default(0);
			$table->float('shipping', 10, 0)->nullable()->default(0);
			$table->float('GrandTotal', 10, 0)->default(0);
			$table->float('paid_amount', 10, 0)->default(0);
			$table->string('payment_statut', 192);
			$table->string('statut');
			$table->text('notes')->nullable();
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
		Schema::drop('sales');
	}

}
