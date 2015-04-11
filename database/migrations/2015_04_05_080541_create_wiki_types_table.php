<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiTypesTable extends Migration {

	public function up()
	{
		Schema::create('wiki_types', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
            $table->string('description');
		});

        \App\Models\Wiki\WikiType::Create([
            'name' => 'page',
            'description' => 'A regular wiki page.'
        ]);

        \App\Models\Wiki\WikiType::Create([
            'name' => 'upload',
            'description' => 'An uploaded file.'
        ]);

        \App\Models\Wiki\WikiType::Create([
            'name' => 'category',
            'description' => 'A category page. Contains an auto-generated list of related pages.'
        ]);
	}

	public function down()
	{
		Schema::drop('wiki_types');
	}

}
