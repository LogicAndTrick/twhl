<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionEnginesTable extends Migration {

	public function up()
	{
		Schema::create('competition_engines', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('competition_id');
            $table->unsignedInteger('engine_id');

            $table->foreign('competition_id')->references('id')->on('competitions');
            $table->foreign('engine_id')->references('id')->on('engines');
		});
	}

	public function down()
	{
		Schema::drop('competition_engines');
	}

}
