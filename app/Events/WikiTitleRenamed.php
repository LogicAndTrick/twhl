<?php

namespace App\Events;

use App\Models\Wiki\WikiObject;
use App\Models\Wiki\WikiRevision;
use Illuminate\Queue\SerializesModels;

class WikiTitleRenamed
{
    use SerializesModels;

    /**
     * @var string
     */
    public $originalTitle;

    /**
     * @var WikiObject
     */
    public $object;

    /**
     * @var WikiRevision
     */
    public $revision;

    /**
     * Create a new event instance.
     *
     * @param $originalTitle string
     * @param $object WikiObject
     * @param $revision WikiRevision
     */
    public function __construct($originalTitle, $object, $revision)
    {
        $this->originalTitle = $originalTitle;
        $this->object = $object;
        $this->revision = $revision;
    }
}
