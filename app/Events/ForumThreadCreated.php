<?php

namespace App\Events;

use App\Models\Forums\ForumThread;
use Illuminate\Queue\SerializesModels;

class ForumThreadCreated
{
    use SerializesModels;

    public ForumThread $thread;

    public function __construct(ForumThread $thread)
    {
        $this->thread = $thread;
    }
}
