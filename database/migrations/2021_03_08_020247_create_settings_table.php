<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('settings', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->integer('id', true);
			$table->string('email', 191);
			$table->integer('currency_id')->nullable()->index('currency_id');
			$table->string('CompanyName');
			$table->string('CompanyPhone');
			$table->string('CompanyAdress');
			$table->string('logo', 191)->nullable();
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
		Schema::drop('settings');
	}

}
