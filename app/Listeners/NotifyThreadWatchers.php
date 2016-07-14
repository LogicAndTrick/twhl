<?php

namespace App\Listeners;

use App\Events\ForumPostCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyThreadWatchers
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
     * @param  ForumPostCreated  $event
     * @return void
     */
    public function handle(ForumPostCreated $event)
    {
        //
    }
}
