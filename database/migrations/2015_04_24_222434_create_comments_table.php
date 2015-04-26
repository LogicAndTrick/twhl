<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration {

	public function up()
	{
		Schema::create('comments', function(Blueprint $table)
		{
			$table->increments('id');
            $table->char('article_type', 1);
            $table->unsignedInteger('article_id');
            $table->unsignedInteger('user_id');
            $table->text('content_text');
            $table->text('content_html');
			$table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->index(['article_type', 'article_id']);
		});
	}

	public function down()
	{
		Schema::drop('comments');
	}

}
