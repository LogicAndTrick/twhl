<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollItemsTable extends Migration {

	public function up()
	{
		Schema::create('poll_items', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('poll_id');
            $table->string('text');
            $table->unsignedInteger('stat_votes');

            $table->foreign('poll_id')->references('id')->on('polls');
		});
	}

	public function down()
	{
		Schema::drop('poll_items');
	}

}
