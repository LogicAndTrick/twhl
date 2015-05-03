<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionRulesTable extends Migration {

	public function up()
	{
		Schema::create('competition_rules', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('competition_id');
            $table->unsignedInteger('restriction_id');

            $table->foreign('competition_id')->references('id')->on('competitions');
            $table->foreign('restriction_id')->references('id')->on('competition_restrictions');
		});
	}

	public function down()
	{
		Schema::drop('competition_rules');
	}

}
