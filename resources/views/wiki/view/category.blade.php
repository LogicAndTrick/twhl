<h1>
    <span class="fa fa-list-ul"></span>
    Category: {{ str_replace('_', ' ', str_replace('+', ' > ', $cat_name)) }}

    @if ($revision)
        <small class="pull-right">
            @if (!$revision->wiki_object->canEdit())
                <span class="fa fa-lock" title="You do not have access to edit this page."></span>
            @elseif ($revision->wiki_object->isProtected())
                <span class="fa fa-lock faded" title="This page is protected."></span>
            @endif
            Last edited @date($revision->created_at) by @avatar($revision->user inline)
        </small>
    @endif
</h1>

<ol class="breadcrumb">
    <li><a href="{{ url('/wiki') }}">Wiki</a></li>
    <li class="active">View Category</li>
</ol>

@if ($revision)
    @include('wiki.view.revision-content', ['revision' => $revision])
@elseif (strpos($cat_name, '+') !== false)
    <div class="card card-outline-info">
        <div class="card-body">
            This is a subcategory page, all pages matching the selected categories will be shown.
        </div>
    </div>
@else
    <div class="card card-outline-info">
        <div class="card-body">
            No information for this category has been entered yet. You can change this by creating the category page by
            <a href="{{ act('wiki', 'create', 'category:'.$cat_name) }}">clicking here</a>.
        </div>
    </div>
@endif

@if (count($subcats) > 0)
    <h4>Subcategories</h4>
    <ul class="columns-3">
        @foreach ($subcats as $sc)
            <li><a href="{{ act('wiki', 'page', 'category:' . $cat_name . '+' . $sc->name) }}">{{ str_replace('_', ' ', $sc->name) }}</a> ({{ $sc->num }})</li>
        @endforeach
    </ul>
@endif

<h4>Pages in this category</h4>

<ul class="columns-2">
    @foreach ($cat_pages as $page)
        <li><a href="{{ act('wiki', 'page', $page->slug) }}">{{ $page->title }}</a></li>
    @endforeach
</ul>

{!! $cat_pages->render() !!}

@include('wiki.view.revision-categories', ['revision' => $revision])