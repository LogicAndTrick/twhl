<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumThreadsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('forum_threads', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('forum_id');
            $table->unsignedInteger('user_id');
            $table->string('title', 200);
            $table->integer('stat_views');
            $table->integer('stat_posts');
            $table->integer('last_post_id');
			$table->timestamp('last_post_at')->nullable();
            $table->boolean('is_open');
            $table->boolean('is_sticky');
			$table->timestamps();
            $table->softDeletes();

            $table->foreign('forum_id')->references('id')->on('forums');
            $table->foreign('user_id')->references('id')->on('users');

            $table->index('forum_id');
            $table->index('user_id');
            $table->index('last_post_id');
            $table->index('last_post_at');
		});

        DB::unprepared("ALTER TABLE forum_threads ADD FULLTEXT forum_threads_title_fulltext (title);");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('forum_threads');
	}

}
