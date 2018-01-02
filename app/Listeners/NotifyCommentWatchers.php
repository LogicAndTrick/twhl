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

        UserNotification::AddNotification(
            $event->comment->user_id,
            $type,
            $event->comment->article_id,
            $event->comment->id
        );
    }
}
