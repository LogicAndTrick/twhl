<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionTypesTable extends Migration {

	public function up()
	{
		Schema::create('competition_types', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
		});

        \App\Models\Competitions\CompetitionType::Create([ 'name' => 'Full Map' ]);
        \App\Models\Competitions\CompetitionType::Create([ 'name' => 'Map from Base' ]);
        \App\Models\Competitions\CompetitionType::Create([ 'name' => 'Screenshot Battle' ]);
        \App\Models\Competitions\CompetitionType::Create([ 'name' => 'Mini Competition' ]);
	}

	public function down()
	{
		Schema::drop('competition_types');
	}

}
