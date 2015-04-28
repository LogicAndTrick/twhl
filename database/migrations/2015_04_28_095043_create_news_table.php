<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsTable extends Migration {

	public function up()
	{
		Schema::create('news', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('title');
            $table->text('content_text');
            $table->text('content_html');
            $table->integer('stat_comments');
            $table->boolean('flag_locked');
			$table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
		});
	}

	public function down()
	{
		Schema::drop('news');
	}

}
