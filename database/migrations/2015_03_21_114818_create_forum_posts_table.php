<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumPostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('forum_posts', function(Blueprint $table)
		{
            $table->increments('id');
            $table->unsignedInteger('forum_id');
            $table->unsignedInteger('thread_id');
            $table->unsignedInteger('user_id');
            $table->text('content_text');
            $table->text('content_html');
			$table->timestamps();
            $table->softDeletes();

            $table->foreign('forum_id')->references('id')->on('forums');
            $table->foreign('thread_id')->references('id')->on('forum_threads');
            $table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('forum_posts');
	}

}
