<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionJudgeTypesTable extends Migration {

	public function up()
	{
		Schema::create('competition_judge_types', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
		});

        \App\Models\Competitions\CompetitionJudgeType::Create([ 'name' => 'Judged (each engine separate)' ]);
        \App\Models\Competitions\CompetitionJudgeType::Create([ 'name' => 'Judged (all engines combined)' ]);
        \App\Models\Competitions\CompetitionJudgeType::Create([ 'name' => 'Community Vote' ]);
	}

	public function down()
	{
		Schema::drop('competition_judge_types');
	}

}
