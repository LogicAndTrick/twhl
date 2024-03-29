
<ul>
    @foreach($data as $rev)
        <li>
            @if (isset($link_to_revision) && $link_to_revision === true)
                <a href="{{ url('wiki/page/' . $rev->slug . '/' . $rev->id) }}">{{ $rev->title }}</a>
            @else
                <a href="{{ url('wiki/page/' . $rev->slug) }}">{{ $rev->title }}</a>
            @endif
            @if (isset($num_revisions) && $num_revisions === true)
                &mdash;
                <em>{{ $rev->num_revisions }} revision{{ $rev->num_revisions == 1 ? '' : 's' }}</em>
            @endif
            @if (isset($content_length) && $content_length === true)
                &mdash;
                <em>{{ $rev->content_length }} character{{ $rev->content_length == 1 ? '' : 's' }}</em>
            @endif
            @if (isset($missing_link) && $missing_link === true)
                &mdash;
                <em>links to <a href="{{ url('wiki/page/' . \App\Models\Wiki\WikiRevision::CreateSlug($rev->missing_link)) }}">{{ $rev->missing_link }}</a></em>
            @endif
            @if (isset($message) && $message === true)
                &bull;
                @date($rev->created_at)
                by @avatar($rev->user inline)
                @if ($rev->message)
                    &mdash;
                    <em>{{ $rev->message }}</em>
                @endif
            @endif
            @if(isset($details_callable))
<?php
                    $details = call_user_func($details_callable, $rev);
?>
                @if ($details && count($details) > 0)
                    <ul>
                        @foreach($details as $d)
                            <li>{{$d}}</li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </li>
    @endforeach
</ul>