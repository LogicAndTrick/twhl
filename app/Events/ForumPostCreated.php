<?php

namespace App\Events;

use App\Events\Event;
use App\Models\Forums\ForumPost;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ForumPostCreated extends Event
{
    use SerializesModels;

    /**
     * @var ForumPost
     */
    public $post;

    public function __construct(ForumPost $post)
    {
        $this->post = $post;
    }
}
