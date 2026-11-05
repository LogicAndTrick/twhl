<?php

namespace App\Events;

use App\Events\Event;
use App\Models\Shout;
use Illuminate\Queue\SerializesModels;

class ShoutCreated extends Event
{
    use SerializesModels;

    public Shout $shout;

    public function __construct(Shout $shout)
    {
        $this->shout = $shout;
    }
}
