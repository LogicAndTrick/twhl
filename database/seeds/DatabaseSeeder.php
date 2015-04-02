<?php

foreach (glob(__DIR__.'/*.php') as $f) {
    require_once($f);
}

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	public function run()
	{
		Model::unguard();

        $this->call('DeleteEverythingSeeder');
        $this->call('UserTableSeeder');
        $this->call('ForumTableSeeder');
	}

}
