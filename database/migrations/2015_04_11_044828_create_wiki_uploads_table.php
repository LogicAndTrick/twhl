<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiUploadsTable extends Migration {

	public function up()
	{
		Schema::create('wiki_uploads', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('object_id');
            $table->unsignedInteger('revision_id');
            $table->string('extension', 4);
			$table->timestamps();

            $table->foreign('object_id')->references('id')->on('wiki_objects');
            $table->foreign('revision_id')->references('id')->on('wiki_revisions');
		});
	}

	public function down()
	{
		Schema::drop('wiki_uploads');
	}

}
