<div>
    @if (isset($revision) && $revision)
        <a href="{{ act('wiki', 'page', $revision->slug) }}">{{ $revision->title }}</a> |
        @if (permission('WikiCreate'))
            <a href="{{ act('wiki', 'edit', $revision->slug) }}">Edit</a> |
        @endif
        <a href="{{ act('wiki', 'history', $revision->slug) }}">History</a> |
    @endif
    @if (permission('ForumCreate'))
        <a href="{{ act('wiki', 'create') }}">Create a new page</a> |
        <a href="{{ act('wiki', 'create-upload') }}">Upload a new file</a> |
    @endif
    <a href="{{ act('wiki', 'pages') }}">See all pages</a> |
    <a href="{{ act('wiki', 'uploads') }}">See all uploads</a> |
    <a href="{{ act('wiki', 'categories') }}">See all categories</a>
</div>