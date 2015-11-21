<hc>
    <span class="pull-right">
        Last edited {{ $revision->created_at->diffForHumans() }} by @avatar($revision->user inline)
    </span>
    <h1>{{ $revision->getNiceTitle() }}</h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/wiki') }}">Wiki</a></li>
        <li class="active">View Page</li>
    </ol>
</hc>
@include('wiki.view.revision-content', ['revision' => $revision])
@include('wiki.view.revision-categories', ['revision' => $revision])