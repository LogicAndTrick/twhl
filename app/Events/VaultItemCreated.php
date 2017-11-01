<?php

namespace App\Events;

use App\Events\Event;
use App\Models\Vault\VaultItem;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VaultItemCreated extends Event
{
    use SerializesModels;

    /**
     * @var VaultItem
     */
    public $item;

    public function __construct(VaultItem $item)
    {
        $this->item = $item;
    }
}
