<div class="btn-toolbar wiki-navigation">
    @if (isset($revision) && $revision)
        <div class="btn-group" role="group">
            <a class="btn btn-default" href="{{ act('wiki', 'page', $revision->slug) }}">{{ $revision->getNiceTitle() }}</a>
            @if (permission('WikiCreate'))
                <a class="btn btn-default" href="{{ act('wiki', 'edit', $revision->slug) }}">Edit</a>
            @endif
            <a class="btn btn-default" href="{{ act('wiki', 'history', $revision->slug) }}">History</a>
        </div>
    @elseif (isset($cat_name) && $cat_name != null)
        <div class="btn-group" role="group">
            <a class="btn btn-default" href="{{ act('wiki', 'page', 'category:'.$cat_name) }}">Category: {{ $cat_name }}</a>
            @if (permission('WikiCreate'))
                <a class="btn btn-default" href="{{ act('wiki', 'create', 'category:'.$cat_name) }}">Edit</a>
            @endif
        </div>
    @endif
    @if (permission('ForumCreate'))
        <div class="btn-group" role="group">
            <a class="btn btn-default" href="{{ act('wiki', 'create') }}">Create a new page</a>
            <a class="btn btn-default" href="{{ act('wiki', 'create-upload') }}">Upload a new file</a>
        </div>
    @endif
    <div class="btn-group" role="group">
        <a class="btn btn-default" href="{{ act('wiki', 'pages') }}">See all pages</a>
        <a class="btn btn-default" href="{{ act('wiki', 'uploads') }}">See all uploads</a>
        <a class="btn btn-default" href="{{ act('wiki', 'categories') }}">See all categories</a>
    </div>
</div>