<h2>User: {{ $user->name }}</h2>
<div class="row">
    <div class="col-md-3 text-center">
        @include('user._avatar', [ 'user' => $user, 'border' => true ])
        <a href="{{ '#' }}" class="btn btn-xs btn-info"><span class="glyphicon glyphicon-envelope"></span> Send Message</a>
    </div>
    <div class="col-md-9">
        <dl class="dl-horizontal">
            @if ($user->info_name)
                <dt>Name</dt><dd>{{ $user->info_name }}</dd>
            @endif
            @if ($user->show_email || permission('Admin') || (Auth::user() && Auth::user()->id == $user->id))
                <dt>Email</dt><dd><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></dd>
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
    </div>
</div>
@if ($user->info_biography_text)
    <hr/>
    <div class="bbcode">{!! $user->info_biography_html !!}</div>
@endif