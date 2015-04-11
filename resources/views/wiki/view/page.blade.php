<h2>{{ $revision->title }}</h2>
@include('wiki.view.revision-content', ['revision' => $revision])
@include('wiki.view.revision-categories', ['revision' => $revision])