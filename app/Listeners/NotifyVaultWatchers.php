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
        UserNotification::AddNotification(
            $event->item->user_id,
            UserNotification::VAULT_CATEGORY,
            $event->item->category_id,
            $event->item->id
        );
    }
}
