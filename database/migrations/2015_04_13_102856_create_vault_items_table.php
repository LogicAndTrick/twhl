<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVaultItemsTable extends Migration {

	public function up()
	{
		Schema::create('vault_items', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('engine_id');
            $table->unsignedInteger('game_id');
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('license_id');
            $table->string('name');
            $table->text('content_text');
            $table->text('content_html');
            $table->boolean('is_hosted_externally');
            $table->string('file_location', 512);
            $table->integer('file_size');
            $table->boolean('flag_notify');
            $table->boolean('flag_ratings');
            $table->integer('stat_views');
            $table->integer('stat_downloads');
            $table->integer('stat_ratings');
            $table->integer('stat_comments');
            $table->integer('stat_average_rating');
			$table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('engine_id')->references('id')->on('engines');
            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('category_id')->references('id')->on('vault_categories');
            $table->foreign('type_id')->references('id')->on('vault_types');
            $table->foreign('license_id')->references('id')->on('licenses');
		});
	}

	public function down()
	{
		Schema::drop('vault_items');
	}

}
