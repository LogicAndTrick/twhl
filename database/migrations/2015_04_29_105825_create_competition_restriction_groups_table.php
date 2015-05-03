<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionRestrictionGroupsTable extends Migration {

	public function up()
	{
		Schema::create('competition_restriction_groups', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title');
            $table->boolean('is_multiple');
		});

        $groups = [
            [ 'General',        true  ],
            [ 'Custom Content', false ],
            [ 'Packaging',      false ],
            [ 'File Contents',  true  ],
            [ 'Submitting',     false ],
            [ 'Judging',        false ],
            [ 'Allowed Games',  true  ],
            [ 'Game Types',     false ],
        ];

        foreach ($groups as $group) {
            \App\Models\Competitions\CompetitionRestrictionGroup::Create([
                'title' => $group[0],
                'is_multiple' => $group[1]
            ]);
        }
	}

	public function down()
	{
		Schema::drop('competition_restriction_groups');
	}

}
