<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionStatusesTable extends Migration {

	public function up()
	{
		Schema::create('competition_statuses', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
		});

        \App\Models\Competitions\CompetitionStatus::Create([ 'name' => 'Draft' ]);
        \App\Models\Competitions\CompetitionStatus::Create([ 'name' => 'Active' ]);
        \App\Models\Competitions\CompetitionStatus::Create([ 'name' => 'Community Voting' ]);
        \App\Models\Competitions\CompetitionStatus::Create([ 'name' => 'Judging In Progress' ]);
        \App\Models\Competitions\CompetitionStatus::Create([ 'name' => 'Closed' ]);
	}

	public function down()
	{
		Schema::drop('competition_statuses');
	}

}
