<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionResultsTable extends Migration {

	public function up()
	{
		Schema::create('competition_results', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('competition_id');
            $table->unsignedInteger('entry_id');
            $table->unsignedInteger('rank');
            $table->text('content_text');
            $table->text('content_html');
			$table->timestamps();

            $table->foreign('competition_id')->references('id')->on('competitions');
            $table->foreign('entry_id')->references('id')->on('competition_entries');
		});
	}

	public function down()
	{
		Schema::drop('competition_results');
	}

}
