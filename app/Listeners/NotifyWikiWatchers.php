<?php

namespace App\Listeners;

use App\Events\WikiRevisionCreated;
use App\Models\Accounts\UserNotification;
use DB;

class NotifyWikiWatchers
{
    public function __construct()
    {
        //
    }

    public function handle(WikiRevisionCreated $event)
    {
        UserNotification::AddNotification(
            $event->revision->user_id,
            UserNotification::WIKI_REVISION,
            $event->revision->object_id,
            $event->revision->id
        );
    }
}
