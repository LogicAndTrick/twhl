<?php

use \App\Models\Accounts\Permission;
use \App\Models\Accounts\User;

class ShoutTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {
        for ($i = 0; $i <= 100; $i++)
        {
            \App\Models\Shout::create([
                'user_id' => ($i % 10) + 1,
                'content' => 'This is shout #'.$i
            ]);
        }
    }
}
