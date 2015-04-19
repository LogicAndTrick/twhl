<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVaultCategoriesTable extends Migration {

	public function up()
	{
		Schema::create('vault_categories', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
            $table->text('description');
            $table->integer('orderindex');
		});

        \App\Models\Vault\VaultCategory::Create([
            'name' => 'Unfinished Stuff',
            'description' => 'Submit maps that you\'re working on to get ideas and criticism that you can take into account as you work. You can update the map as you go along, then move it to Completed Maps when it is finished.',
            'orderindex' => 1
        ]);

        \App\Models\Vault\VaultCategory::Create([
            'name' => 'Completed Maps',
            'description' => 'Submit finished maps here so that people can comment or review them in their entirety. Of course, you could always turn the \'completed\' map back into work-in-progress as you see the comments. ',
            'orderindex' => 2
        ]);

        \App\Models\Vault\VaultCategory::Create([
            'name' => 'Problem Maps',
            'description' => 'Submit maps that have specific problems here. e.g. You can\'t get a particular entity set-up to work. Submit the .RMF, and others will be able to help. But remember to make a forum or shoutbox post and link to your map, or chances are nobody will bother downloading it!',
            'orderindex' => 3
        ]);

        \App\Models\Vault\VaultCategory::Create([
            'name' => 'Example Maps',
            'description' => 'A place to put maps that demonstrate something or act as answers to or complement forum posts, etc.',
            'orderindex' => 4
        ]);
	}

	public function down()
	{
		Schema::drop('vault_categories');
	}

}
