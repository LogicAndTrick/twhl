
<ul>
    @foreach($data as $rev)
        <li>
            <a href="{{ url('wiki-special/query-links?title=' . urlencode($rev->title)) }}">{{ $rev->title }}</a>
            &mdash;
            <em>Linked to from {{ $rev->link_count }} other page{{ $rev->link_count == 1 ? '' : 's' }}</em>
        </li>
    @endforeach
</ul>