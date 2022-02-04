<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToQuotationDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('quotation_details', function(Blueprint $table)
		{
			$table->foreign('product_id', 'product_id_quotation_details')->references('id')->on('products')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('quotation_id', 'quotation_id')->references('id')->on('quotations')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('product_variant_id', 'quote_product_variant_id')->references('id')->on('product_variants')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('quotation_details', function(Blueprint $table)
		{
			$table->dropForeign('product_id_quotation_details');
			$table->dropForeign('quotation_id');
			$table->dropForeign('quote_product_variant_id');
		});
	}

}
