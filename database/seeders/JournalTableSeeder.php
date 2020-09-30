<?php

namespace Database\Seeders;

use \App\Models\Accounts\Permission;
use \App\Models\Accounts\User;

class JournalTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {
        for ($i = 0; $i <= 100; $i++)
        {
            \App\Models\Journal::create([
                'user_id' => ($i % 10) + 1,
                'content_text' => 'This is test journal #'.$i,
                'content_html' => 'This is test journal #'.$i,
                'stat_comments' => 0,
                'flag_locked' => false
            ]);
        }
    }
}
