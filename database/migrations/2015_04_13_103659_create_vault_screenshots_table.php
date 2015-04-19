<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVaultScreenshotsTable extends Migration {

	public function up()
	{
		Schema::create('vault_screenshots', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('item_id');
            $table->boolean('is_primary');
            $table->text('image_thumb');
            $table->text('image_small');
            $table->text('image_medium');
            $table->text('image_large');
            $table->text('image_full');
            $table->integer('image_size');
			$table->timestamps();

            $table->foreign('item_id')->references('id')->on('vault_items');
		});
	}

	public function down()
	{
		Schema::drop('vault_screenshots');
	}

}
