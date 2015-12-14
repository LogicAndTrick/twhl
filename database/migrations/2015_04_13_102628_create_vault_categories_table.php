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
            'name' => 'Unfinished',
            'description' => 'Submit stuff that you\'re working on to get ideas and criticism that you can '.
            'take into account as you work. You can update the listing as you go along, then move it to the '.
            'Completed section when it is finished. Unfinished items cannot be reviewed, and they and cannot win MOTM.',
            'orderindex' => 1
        ]);

        \App\Models\Vault\VaultCategory::Create([
            'name' => 'Completed',
            'description' => 'Submit finished stuff here so that people can comment or review them in their entirety. '.
            'Items in this category can be reviewed and can win the MOTM award.',
            'orderindex' => 2
        ]);

        \App\Models\Vault\VaultCategory::Create([
            'name' => 'Problems',
            'description' => 'Submit things that have specific problems here. For example: You can\'t get a particular entity '
            .'set-up to work in map. Submit the source files, and others will be able to help. But remember to make a forum or '.
            'shoutbox post and link to your map, or chances are nobody will bother downloading it! These items can\'t be '.
            'reviewed and they can\'t win MOTM',
            'orderindex' => 3
        ]);

        \App\Models\Vault\VaultCategory::Create([
            'name' => 'Examples',
            'description' => 'A place to put items that demonstrate something or act as answers to or complement '.
            'forum posts, wiki articles and so on. Items in this category can\'t be reviewed or win MOTM.',
            'orderindex' => 4
        ]);
	}

	public function down()
	{
		Schema::drop('vault_categories');
	}

}
