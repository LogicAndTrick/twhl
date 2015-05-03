<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionEntryVotesTable extends Migration {

	public function up()
	{
		Schema::create('competition_entry_votes', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('competition_id');
            $table->unsignedInteger('entry_id');
            $table->unsignedInteger('user_id');

            $table->foreign('competition_id')->references('id')->on('competitions');
            $table->foreign('entry_id')->references('id')->on('competition_entries');
            $table->foreign('user_id')->references('id')->on('users');
		});
	}

	public function down()
	{
		Schema::drop('competition_entry_votes');
	}

}
