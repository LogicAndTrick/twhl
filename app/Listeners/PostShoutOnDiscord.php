<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Events\ShoutCreated;

class PostShoutOnDiscord
{
    public function __construct()
    {
        //
    }

    public function handle(ShoutCreated $event): void
    {
        try {
            $discordUrl = config('services.discord-bot.url');
            $discordToken = config('services.discord-bot.token');
            if (!$discordUrl || !$discordToken) {
                return;
            }

            Http::withHeaders(['Authorization' => 'Bearer ' . $discordToken])
                ->timeout(0.1)
                ->post(Str::chopEnd($discordUrl, '/') . '/shout', $event->shout);
        } catch (\Exception $e) {
            // didn't work, who cares.
        }
    }
}
