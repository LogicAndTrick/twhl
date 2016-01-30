<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVaultIncludesTable extends Migration {

	public function up()
	{
		Schema::create('vault_includes', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('type_id');
            $table->string('name');
            $table->string('description');
            $table->integer('orderindex');

            $table->foreign('type_id')->references('id')->on('vault_types');
		});

        \App\Models\Vault\VaultInclude::Create([ 'type_id' => 1, 'name' => 'BSP',       'description' => 'Compiled map',            'orderindex' => 1 ]);
        \App\Models\Vault\VaultInclude::Create([ 'type_id' => 1, 'name' => 'RMF/VMF',   'description' => 'Map source file',         'orderindex' => 2 ]);
        \App\Models\Vault\VaultInclude::Create([ 'type_id' => 2, 'name' => 'MDL',       'description' => 'Compiled model file',     'orderindex' => 3 ]);
        \App\Models\Vault\VaultInclude::Create([ 'type_id' => 3, 'name' => 'WAD',       'description' => 'Goldsource texture WAD',  'orderindex' => 4 ]);
        \App\Models\Vault\VaultInclude::Create([ 'type_id' => 3, 'name' => 'VTF/VMT',   'description' => 'Source texture file',     'orderindex' => 5 ]);
        \App\Models\Vault\VaultInclude::Create([ 'type_id' => 3, 'name' => 'JPG/PSD',   'description' => 'Texture source file',     'orderindex' => 6 ]);
        \App\Models\Vault\VaultInclude::Create([ 'type_id' => 4, 'name' => 'Mod files', 'description' => 'Mod Files',               'orderindex' => 7 ]);
        \App\Models\Vault\VaultInclude::Create([ 'type_id' => 5, 'name' => 'EXE',       'description' => 'Application Executables', 'orderindex' => 8 ]);
        \App\Models\Vault\VaultInclude::Create([ 'type_id' => 6, 'name' => 'Prefab',    'description' => 'Prefab files',            'orderindex' => 9 ]);
	}

	public function down()
	{
		Schema::drop('vault_includes');
	}

}
