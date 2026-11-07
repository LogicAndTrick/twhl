<?php

namespace App\Listeners;

use App\Events\ForumThreadCreated;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Events\ShoutCreated;

class PostOnDiscord
{
    public function __construct()
    {
        //
    }

    public function handle(ShoutCreated|ForumThreadCreated $event): void
    {
        try {
            $discordUrl = config('services.discord-bot.url');
            $discordToken = config('services.discord-bot.token');
            if (!$discordUrl || !$discordToken) {
                return;
            }

            $data = null;
            $endpoint = '';
            if ($event instanceof ForumThreadCreated) {
                // don't broadcast threads from restricted forums
                if ($event->thread->forum->permission_id !== null) return;
                $data = $event->thread;
                $endpoint = '/forum-thread';
            } elseif ($event instanceof ShoutCreated) {
                $data = $event->shout;
                $endpoint = '/shout';
            } else {
                return;
            }

            Http::withHeaders(['Authorization' => 'Bearer ' . $discordToken])
                ->timeout(0.1)
                ->post(Str::chopEnd($discordUrl, '/') . $endpoint, $data);
        } catch (\Exception $e) {
            // didn't work, who cares.
        }
    }
}
