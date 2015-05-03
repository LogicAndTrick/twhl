<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionEntryScreenshotsTable extends Migration {

	public function up()
	{
		Schema::create('competition_entry_screenshots', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('entry_id');
            $table->text('image_thumb');
            $table->text('image_full');

            $table->foreign('entry_id')->references('id')->on('competition_entries');
		});
	}

	public function down()
	{
		Schema::drop('competition_entry_screenshots');
	}

}
