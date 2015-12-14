<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollsTable extends Migration {

	public function up()
	{
		Schema::create('polls', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('title');
            $table->string('content_text');
            $table->string('content_html');
            $table->date('close_date');
            $table->integer('stat_comments');
            $table->boolean('flag_locked');
			$table->timestamps();
            $table->softDeletes();
		});
	}

	public function down()
	{
		Schema::drop('polls');
	}

}
