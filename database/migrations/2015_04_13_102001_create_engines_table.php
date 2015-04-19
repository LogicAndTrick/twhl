<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnginesTable extends Migration {

	public function up()
	{
		Schema::create('engines', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
            $table->integer('orderindex');
		});

        \App\Models\Engine::Create([
            'name' => 'Goldsource',
            'orderindex' => 1
        ]);

        \App\Models\Engine::Create([
            'name' => 'Source',
            'orderindex' => 2
        ]);

        \App\Models\Engine::Create([
            'name' => 'Other',
            'orderindex' => 100
        ]);
	}

	public function down()
	{
		Schema::drop('engines');
	}

}
