<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiRevisionMetasTable extends Migration {

	public function up()
	{
		Schema::create('wiki_revision_metas', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('revision_id');
            $table->char('key', 1);
            $table->string('value');

            $table->foreign('revision_id')->references('id')->on('wiki_revisions');
            $table->index(['key', 'value']);
			$table->index(['revision_id', 'key']);
		});
	}

	public function down()
	{
		Schema::drop('wiki_revision_metas');
	}

}
