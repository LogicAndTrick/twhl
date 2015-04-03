<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumsTable extends Migration {

	public function up()
	{
        Schema::create('forums', function(Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 15);
            $table->string('name');
            $table->text('description');
            $table->text('permission_id')->nullable();
            $table->integer('stat_threads');
            $table->integer('stat_posts');
            $table->unsignedInteger('last_post_id');
            $table->integer('order_index');
            $table->timestamps();
            $table->softDeletes();
        });
	}

	public function down()
	{
		Schema::drop('forums');
	}

}
