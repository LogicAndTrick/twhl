<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageThreadUsersTable extends Migration {

	public function up()
	{
		Schema::create('message_thread_users', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('thread_id');
            $table->unsignedInteger('user_id');
			$table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('thread_id')->references('id')->on('message_threads');
		});
	}

	public function down()
	{
		Schema::drop('message_thread_users');
	}

}
