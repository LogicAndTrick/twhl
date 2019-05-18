
<ul>
    @foreach($data as $user)
        <li>
            @avatar($user inline)
            @if (isset($stat_wiki_edits) && $stat_wiki_edits === true)
                &mdash;
                <em>{{ $user->stat_wiki_edits }} edit{{ $user->stat_wiki_edits == 1 ? '' : 's' }}</em>
            @endif
        </li>
    @endforeach
</ul>