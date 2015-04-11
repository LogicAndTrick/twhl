@extends('app')

@section('content')
    @include('wiki.nav', ['revision' => $revision])
    <h2>{{ $revision->title }}</h2>
    @form(wiki/edit)
        @hidden(id $revision)
        @text(title $revision) = Page Title
        @textarea(content_text $revision) = Page Content
        @text(message) = Description of Edit
        @submit = Edit Page
    @endform
@endsection
