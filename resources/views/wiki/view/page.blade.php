<hc>
    <span class="pull-right">
        @if (!$revision->wiki_object->canEdit())
            <span class="fa fa-lock" title="You do not have access to edit this page."></span>
        @elseif ($revision->wiki_object->isProtected())
            <span class="fa fa-lock faded" title="This page is protected."></span>
        @endif
        Last edited @date($revision->created_at) by @avatar($revision->user inline)
    </span>
    <h1>{{ $revision->getNiceTitle() }}</h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/wiki') }}">Wiki</a></li>
        <li class="active">View Page</li>
    </ol>
</hc>
@include('wiki.view.revision-content', ['revision' => $revision])
@include('wiki.view.revision-categories', ['revision' => $revision])