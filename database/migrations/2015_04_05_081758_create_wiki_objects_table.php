<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiObjectsTable extends Migration {

	public function up()
	{
		Schema::create('wiki_objects', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('type_id');
            $table->string('current_revision_id');
            $table->unsignedInteger('permission_id')->nullable();
            $table->integer('stat_comments');
            $table->boolean('flag_locked');
			$table->timestamps();
            $table->softDeletes();

            $table->foreign('type_id')->references('id')->on('wiki_types');
		});
	}

	public function down()
	{
		Schema::drop('wiki_objects');
	}

}
