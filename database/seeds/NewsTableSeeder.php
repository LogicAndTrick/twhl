<?php

use \App\Models\Accounts\Permission;
use \App\Models\Accounts\User;

class NewsTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {
        for ($i = 0; $i <= 100; $i++)
        {
            \App\Models\News::create([
                'user_id' => ($i % 10) + 1,
                'title' => 'Test news #'.$i,
                'content_text' => 'This is test news #'.$i,
                'content_html' => 'This is test news #'.$i,
                'stat_comments' => 0,
                'flag_locked' => false
            ]);
        }
    }
}
