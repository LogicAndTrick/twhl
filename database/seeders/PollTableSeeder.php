<?php

namespace Database\Seeders;

use \App\Models\Accounts\Permission;
use \App\Models\Accounts\User;

class PollTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {
        for ($i = 0; $i <= 20; $i++)
        {
            $poll = \App\Models\Polls\Poll::create([
                'title' => 'Poll '.$i,
                'content_text' => 'This is poll #'.$i,
                'content_html' => 'This is poll #'.$i,
                'close_date' => \Carbon\Carbon::now()->addDay($i)->setTime(0, 0, 0)
            ]);

            $ids = [];
            for ($j = 0; $j < 5; $j++) {
                $item = \App\Models\Polls\PollItem::create([
                    'poll_id' => $poll->id,
                    'text' => 'Option '.$j
                ]);
                $ids[] = $item->id;
            }

            for ($k = 1; $k <= 10; $k++) {
                $id = $ids[array_rand($ids, 1)];
                $vote = \App\Models\Polls\PollItemVote::create([
                    'poll_id' => $poll->id,
                    'item_id' => $id,
                    'user_id' => $k
                ]);
            }
        }
    }
}
