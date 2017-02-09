<div class="row">
    <div class="col-md-4 text-center">
        @avatar($user full show_border=true)
        <a href="{{ act('message', 'send', $user->id) }}" class="btn btn-xs btn-info"><span class="fa fa-envelope"></span> Send Message</a>
        <hr title="Stats"/>
        <dl class="dl-horizontal dl-small">
            {? $age = $user->created_at->diffInDays(); if ($age == 0) $age = 1; ?}
            <dt>Logins</dt>
            <dd>{{ $user->stat_logins }} ({{ round($user->stat_logins / $age, 2) }} per day)</dd>
            <dt>Profile Hits</dt>
            <dd>{{ $user->stat_profile_hits }} ({{ round($user->stat_profile_hits / $age, 2) }})</dd>
            <dt>Forum Posts</dt>
            <dd><a href="{{ act('post', 'index').'?user='.$user->id }}">{{ $user->stat_forum_posts }} ({{ round($user->stat_forum_posts / $age, 2) }})</a></dd>
            <dt>Vault Items</dt>
            <dd><a href="{{ act('vault', 'index').'?users='.$user->id }}">{{ $user->stat_vault_items }} ({{ round($user->stat_vault_items / $age, 2) }})</a></dd>
            <dt>Journals</dt>
            <dd><a href="{{ act('journal', 'index').'?user='.$user->id }}">{{ $user->stat_journals }} ({{ round($user->stat_journals / $age, 2) }})</a></dd>
            <dt>Wiki Edits</dt>
            <dd>{{ $user->stat_wiki_edits }} ({{ round($user->stat_wiki_edits / $age, 2) }})</dd>
            <dt>Comments</dt>
            <dd>{{ $user->stat_comments }} ({{ round($user->stat_comments / $age, 2) }})</dd>
            <dt>Shouts</dt>
            <dd>{{ $user->stat_shouts }} ({{ round($user->stat_shouts / $age, 2) }})</dd>
        </dl>
    </div>
    <div class="col-md-8">
        <dl class="dl-horizontal">
            @if ($user->info_name)
                <dt>Name</dt><dd>{{ $user->info_name }}</dd>
            @endif
            <dt>Joined</dt><dd>{{ $user->created_at->format('jS F Y') }} (@date($user->created_at))</dd>
            <dt>Last Visited</dt><dd>@date($user->last_access_time)</dd>
            @if ($user->show_email || permission('Admin') || (Auth::user() && Auth::user()->id == $user->id))
                <dt>Email</dt>
                <dd>
                    <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                    @if (permission('Admin') || (Auth::user() && Auth::user()->id == $user->id))
                        <span class="label label-default">
                            @if ($user->show_email)
                                <span class="fa fa-check"></span> Visible on public profile
                            @else
                                <span class="fa fa-remove"></span> Hidden on public profile
                            @endif
                        </span>
                    @endif
                </dd>
            @endif
            @if ($user->info_website)
                <dt>Website</dt><dd><a href="{{ $user->info_website }}">{{ $user->info_website }}</a></dd>
            @endif
            @if ($user->info_occupation)
                <dt>Occupation</dt><dd>{{ $user->info_occupation }}</dd>
            @endif
            @if ($user->info_interests)
                <dt>Interests</dt><dd>{{ $user->info_interests }}</dd>
            @endif
            @if ($user->info_location)
                <dt>Location</dt><dd>{{ $user->info_location }}</dd>
            @endif
            @if ($user->info_languages)
                <dt>Languages</dt><dd>{{ $user->info_languages }}</dd>
            @endif
            @if ($user->info_birthday_formatted)
                <dt>Birthday</dt><dd>{{ $user->info_birthday_formatted }}</dd>
            @endif
            @if ($user->info_steam_profile)
                <dt>Steam Profile</dt><dd><a href="https://steamcommunity.com/id/{{ $user->info_steam_profile }}">{{ $user->info_steam_profile }}</a></dd>
            @endif
            @if ($user->hasSkills())
                <dt>Skills</dt><dd>{{ implode(', ', $user->getSkills()) }}</dd>
            @endif
            {? $history = $user->getPreviousAliases(); ?}
            @if (count($history) > 0)
                <dt>Previous Names</dt><dd>{{ implode(', ', $history) }}</dd>
            @endif
        </dl>
        @if (permission('Admin'))
            <hr title="Admin" />
            <dl class="dl-horizontal">
                <dt>Timezone</dt><dd>UTC{{ ($user->timezone >= 0 ? '+' : '') }}{{ $user->timezone }}</dd>
                <dt>Last Page</dt><dd>{{ $user->last_access_page }}</dd>
                <dt>Last IP</dt><dd>{{ $user->last_access_ip }}</dd>
            </dl>
        @endif
    </div>
</div>
@if ($user->info_biography_text)
    <hr title="Biography"/>
    <div class="bbcode">{!! $user->info_biography_html !!}</div>
@endif