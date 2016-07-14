<?php

namespace App\Listeners;

use App\Events\WikiRevisionCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyWikiWatchers
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
     * @param  WikiRevisionCreated  $event
     * @return void
     */
    public function handle(WikiRevisionCreated $event)
    {
        //
    }
}
