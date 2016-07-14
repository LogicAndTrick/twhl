<?php

namespace App\Listeners;

use App\Events\VaultItemCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyVaultWatchers
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  VaultItemCreated  $event
     * @return void
     */
    public function handle(VaultItemCreated $event)
    {
        //
    }
}
