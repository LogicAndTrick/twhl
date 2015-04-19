<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVaultTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vault_types', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
            $table->integer('orderindex');
		});

        \App\Models\Vault\VaultType::Create([
            'name' => 'Map',
            'orderindex' => 1
        ]);

        \App\Models\Vault\VaultType::Create([
            'name' => 'Model',
            'orderindex' => 2
        ]);

        \App\Models\Vault\VaultType::Create([
            'name' => 'Texture',
            'orderindex' => 3
        ]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vault_types');
	}

}
