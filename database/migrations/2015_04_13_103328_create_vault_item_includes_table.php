<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVaultItemIncludesTable extends Migration {

	public function up()
	{
		Schema::create('vault_item_includes', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('include_id');

            $table->foreign('item_id')->references('id')->on('vault_items');
            $table->foreign('include_id')->references('id')->on('vault_includes');
		});
	}

	public function down()
	{
		Schema::drop('vault_item_includes');
	}

}
