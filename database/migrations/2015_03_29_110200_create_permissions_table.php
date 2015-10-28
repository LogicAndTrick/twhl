<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionsTable extends Migration {

	public function up()
	{
		Schema::create('permissions', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name', 20);
            $table->string('description');
            $table->boolean('is_default');
		});

        $non_default = [
            'Admin' => 'General admin access',
            'ForumAdmin' => 'Create, edit, and delete forums, threads, and posts',
            'WikiAdmin' => 'Edit locked wiki pages, delete wiki pages and uploads',
            'VaultAdmin' => 'Edit and delete all vault items',
            'NewsAdmin' => 'Create, edit, and delete news posts',
            'JournalAdmin' => 'Edit and delete all journals',
            'CompetitionAdmin' => 'Create, edit, delete and perform administrative actions on competitions',
            'PollAdmin' => 'Create, edit, and delete polls'
        ];
        $default = [
            'ForumCreate' => 'Create threads and posts in the forums',
            'WikiCreate' => 'Create and edit wiki pages and uploads',
            'VaultCreate' => 'Create and edit vault items',
            'JournalCreate' => 'Create and edit journals',
            'CompetitionEnter' => 'Enter and vote on competitions',

            'NewsComment' => 'Comment on news posts',
            'VaultComment' => 'Comment on vault items',
            'JournalComment' => 'Comment on journals',
            'PollComment' => 'Comment on polls',
            'WikiComment' => 'Comment on wiki articles'
        ];

        foreach ($default as $name => $description) {
            \App\Models\Accounts\Permission::Create([ 'name' => $name, 'description' => $description, 'is_default' => true ]);
        }
        foreach ($non_default as $name => $description) {
            \App\Models\Accounts\Permission::Create([ 'name' => $name, 'description' => $description, 'is_default' => false ]);
        }
	}

	public function down()
	{
		Schema::drop('permissions');
	}

}
