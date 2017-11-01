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
        $type = UserNotification::FORUM_THREAD;

        $id = $event->post->thread_id;
        $user_id = $event->post->user_id;

        DB::statement(
            'INSERT INTO user_notifications (user_id, article_type, article_id, is_unread, is_processed, created_at, updated_at)
            SELECT US.user_id, US.article_type, US.article_id, 1, 0, UTC_TIMESTAMP(), UTC_TIMESTAMP()
            FROM user_subscriptions US
            WHERE US.article_type = ? AND US.article_id = ? AND US.user_id != ?', [$type, $id, $user_id]);
    }
}
