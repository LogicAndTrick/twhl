<?php

namespace App\Listeners;

use App\Events\ForumPostCreated;
use App\Models\Accounts\UserNotification;
use DB;

class NotifyThreadWatchers
{
    public function __construct()
    {
        //
    }

    public function handle(ForumPostCreated $event)
    {
        UserNotification::AddNotification(
            $event->post->user_id,
            UserNotification::FORUM_THREAD,
            $event->post->thread_id,
            $event->post->id
        );
    }
}
