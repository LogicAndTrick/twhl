<?php

namespace Database\Seeders;

use \App\Models\Accounts\Permission;
use \App\Models\Accounts\User;

class MessageTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {
        for ($i = 0; $i < 25; $i++) {
            $thread = \App\Models\Messages\MessageThread::create([
                'user_id' => ($i % 3) + 1,
                'subject' => 'Message thread #'.$i,
                'last_message_id' => 0
            ]);

            $message = null;
            for ($j = 0; $j < 4; $j++) {
                $message = \App\Models\Messages\Message::create([
                    'user_id' => ($j % 3) + 1,
                    'thread_id' => $thread->id,
                    'content_text' => 'This is message #'.$j,
                    'content_html' => 'This is message #'.$j
                ]);

                for ($k = 1; $k <= 3; $k++) {
                    \App\Models\Messages\MessageUser::create([
                        'thread_id' => $thread->id,
                        'message_id' => $message->id,
                        'user_id' => $k,
                        'is_unread' => (($j % 3) + 1) != $k
                    ]);
                }
            }
            $thread->update([
                'last_message_id' => $message->id
            ]);

            for ($k = 1; $k <= 3; $k++) {
                \App\Models\Messages\MessageThreadUser::create([
                    'thread_id' => $thread->id,
                    'user_id' => $k
                ]);
            }
        }
    }
}
