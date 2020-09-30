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
        $this->call('WikiTableSeeder');
        $this->call('VaultTableSeeder');
        $this->call('ShoutTableSeeder');
        $this->call('PollTableSeeder');
        $this->call('NewsTableSeeder');
        $this->call('MessageTableSeeder');
        $this->call('ForumTableSeeder');
        $this->call('JournalTableSeeder');
        $this->call('CompetitionTableSeeder');
	}

}
