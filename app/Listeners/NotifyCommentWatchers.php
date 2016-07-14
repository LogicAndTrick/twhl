<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Models\Accounts\UserNotification;
use DB;

class NotifyCommentWatchers
{
    public function __construct()
    {
        //
    }

    public function handle(CommentCreated $event)
    {
        $type = UserNotification::GetTypeFromCommentType($event->comment->article_type);
        if ($type == null) return;

        $id = $event->comment->article_id;

        DB::statement(
            'INSERT INTO user_notifications (user_id, article_type, article_id, is_unread, is_processed, created_at, updated_at)
            SELECT US.user_id, US.article_type, US.article_id, 1, 0, UTC_TIMESTAMP(), UTC_TIMESTAMP()
            FROM user_subscriptions US
            WHERE US.article_type = ? AND US.article_id = ?', [$type, $id]);
    }
}
