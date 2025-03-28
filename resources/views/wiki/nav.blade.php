<div class="btn-toolbar wiki-navigation">
    @if (isset($revision) && $revision)
        <div class="btn-group" role="group">
            <a class="btn btn-near" href="{{ act('wiki', 'page', $latest_revision->slug) }}">{{ $latest_revision->getNiceTitle() }}</a>
            @if (permission('WikiCreate') && $revision->wiki_object->canEdit())
                <a class="btn btn-near" href="{{ act('wiki', 'edit', $latest_revision->slug) }}"><span class="fa fa-pencil"></span> Edit</a>
            @endif
            <a class="btn btn-near" href="{{ act('wiki', 'history', $latest_revision->slug) }}"><span class="fa fa-clock-o"></span> History</a>
        </div>
    @elseif (isset($cat_name) && strpos($cat_name, '+') !== false)
        <div class="btn-group" role="group">
            <a class="btn btn-near" href="{{ act('wiki', 'page', 'category:'.$cat_name) }}">Subcategory</a>
        </div>
    @elseif (isset($cat_name) && $cat_name != null)
        <div class="btn-group" role="group">
            <a class="btn btn-near" href="{{ act('wiki', 'page', 'category:'.$cat_name) }}">Category: {{ str_replace('_', ' ', $cat_name) }}</a>
            @if (permission('WikiCreate'))
                <a class="btn btn-near" href="{{ act('wiki', 'create', 'category:'.$cat_name) }}"><span class="fa fa-pencil"></span> Edit</a>
            @endif
        </div>
    @endif
    @if (permission('ForumCreate'))
        <div class="btn-group" role="group">
            <a class="btn btn-near" href="{{ act('wiki', 'create') }}"><span class="fa fa-plus"></span> Create a new page</a>
            <a class="btn btn-near" href="{{ act('wiki', 'create-upload') }}"><span class="fa fa-arrow-up"></span> Upload a new file</a>
        </div>
    @endif
    <div class="btn-group" role="group">
        <a class="btn btn-near" href="{{ act('wiki', 'pages') }}">See all pages</a>
        <a class="btn btn-near" href="{{ act('wiki', 'uploads') }}">See all uploads</a>
        <a class="btn btn-near" href="{{ act('wiki', 'categories') }}">See all categories</a>
    </div>
</div>