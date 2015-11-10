<hc>
    <h1>{{ $revision->title }}</h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/wiki') }}">Wiki</a></li>
        <li class="active">View Page</li>
    </ol>
</hc>
@include('wiki.view.revision-content', ['revision' => $revision])
@include('wiki.view.revision-categories', ['revision' => $revision])