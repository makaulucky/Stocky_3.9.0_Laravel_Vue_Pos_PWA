<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPermissionRoleTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('permission_role', function(Blueprint $table)
		{
			$table->foreign('permission_id', 'permission_role_permission_id')->references('id')->on('permissions')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('role_id', 'permission_role_role_id')->references('id')->on('roles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('permission_role', function(Blueprint $table)
		{
			$table->dropForeign('permission_role_permission_id');
			$table->dropForeign('permission_role_role_id');
		});
	}

}
