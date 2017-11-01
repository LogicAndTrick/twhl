<?php

namespace App\Events;

use App\Events\Event;
use App\Models\Wiki\WikiRevision;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WikiRevisionCreated extends Event
{
    use SerializesModels;
    
    /**
     * @var WikiRevision
     */
    public $revision;

    public function __construct(WikiRevision $revision)
    {
        $this->revision = $revision;
    }
}
