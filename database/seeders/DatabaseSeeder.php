<?php

namespace Database\Seeders;

foreach (glob(__DIR__.'/*.php') as $f) {
    require_once($f);
}

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	public function run()
	{
		Model::unguard();

        $this->call(\Database\Seeders\DeleteEverythingSeeder::class);
        $this->call(\Database\Seeders\UserTableSeeder::class);
        $this->call(\Database\Seeders\WikiTableSeeder::class);
        $this->call(\Database\Seeders\VaultTableSeeder::class);
        $this->call(\Database\Seeders\ShoutTableSeeder::class);
        $this->call(\Database\Seeders\PollTableSeeder::class);
        $this->call(\Database\Seeders\NewsTableSeeder::class);
        $this->call(\Database\Seeders\MessageTableSeeder::class);
        $this->call(\Database\Seeders\ForumTableSeeder::class);
        $this->call(\Database\Seeders\JournalTableSeeder::class);
        $this->call(\Database\Seeders\CompetitionTableSeeder::class);
	}

}
