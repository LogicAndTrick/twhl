<div class="row">
    <div class="col-md-4 text-center">
        @include('user._avatar', [ 'user' => $user, 'border' => true ])
        <a href="{{ '#' }}" class="btn btn-xs btn-info"><span class="glyphicon glyphicon-envelope"></span> Send Message</a>
        <hr title="Stats"/>
        <dl class="dl-horizontal dl-small">
            <dt>Logins (per day)</dt>
            <dd>{{ $user->stat_logins }} ({{ round($user->stat_logins / $user->created_at->diffInDays(), 2) }})</dd>
            <dt>Profile Hits</dt>
            <dd>{{ $user->stat_profile_hits }} ({{ round($user->stat_profile_hits / $user->created_at->diffInDays(), 2) }})</dd>
            <dt>Forum Posts</dt>
            <dd><a href="{{ act('user', 'view', $user->id) }}">{{ $user->stat_forum_posts }} ({{ round($user->stat_forum_posts / $user->created_at->diffInDays(), 2) }})</a></dd>
            <dt>Vault Items</dt>
            <dd><a href="{{ act('user', 'view', $user->id) }}">{{ $user->stat_maps }} ({{ round($user->stat_maps / $user->created_at->diffInDays(), 2) }})</a></dd>
            <dt>Journals</dt>
            <dd><a href="{{ act('user', 'view', $user->id) }}">{{ $user->stat_journals }} ({{ round($user->stat_journals / $user->created_at->diffInDays(), 2) }})</a></dd>
            <dt>Wiki Edits</dt>
            <dd>{{ $user->stat_wiki_edits }} ({{ round($user->stat_wiki_edits / $user->created_at->diffInDays(), 2) }})</dd>
            <dt>Comments</dt>
            <dd>{{ $user->stat_comments }} ({{ round($user->stat_comments / $user->created_at->diffInDays(), 2) }})</dd>
            <dt>Shouts</dt>
            <dd>{{ $user->stat_shouts }} ({{ round($user->stat_shouts / $user->created_at->diffInDays(), 2) }})</dd>
        </dl>
    </div>
    <div class="col-md-8">
        <dl class="dl-horizontal">
            @if ($user->info_name)
                <dt>Name</dt><dd>{{ $user->info_name }}</dd>
            @endif
            <dt>Joined</dt><dd>{{ $user->created_at->format('jS F Y') }} ({{ $user->created_at->diffForHumans() }})</dd>
            <dt>Last Visited</dt><dd>{{ $user->last_access_time ? $user->last_access_time->diffForHumans() : 'Never' }}</dd>
            @if ($user->show_email || permission('Admin') || (Auth::user() && Auth::user()->id == $user->id))
                <dt>Email</dt>
                <dd>
                    <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                    @if (permission('Admin') || (Auth::user() && Auth::user()->id == $user->id))
                        <span class="label label-default">
                            @if ($user->show_email)
                                <span class="glyphicon glyphicon-ok"></span> Visible on public profile
                            @else
                                <span class="glyphicon glyphicon-remove"></span> Hidden on public profile
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