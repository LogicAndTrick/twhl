<?php

namespace App\Listeners;

use App\Events\VaultItemCreated;
use App\Models\Accounts\UserNotification;
use DB;

class NotifyVaultWatchers
{
    public function __construct()
    {
        //
    }

    public function handle(VaultItemCreated $event)
    {
        $type = UserNotification::VAULT_CATEGORY;

        $id = $event->item->category_id;
        $user_id = $event->item->user_id;

        DB::statement(
            'INSERT INTO user_notifications (user_id, article_type, article_id, is_unread, is_processed, created_at, updated_at)
            SELECT US.user_id, US.article_type, US.article_id, 1, 0, UTC_TIMESTAMP(), UTC_TIMESTAMP()
            FROM user_subscriptions US
            WHERE US.article_type = ? AND US.article_id = ? AND US.user_id != ?', [$type, $id, $user_id]);
    }
}
