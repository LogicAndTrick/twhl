<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageUsersTable extends Migration {

	public function up()
	{
		Schema::create('message_users', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('thread_id');
            $table->unsignedInteger('message_id');
            $table->unsignedInteger('user_id');
            $table->boolean('is_unread');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('thread_id')->references('id')->on('message_threads');
            $table->foreign('message_id')->references('id')->on('messages');
		});
	}

	public function down()
	{
		Schema::drop('message_users');
	}

}
