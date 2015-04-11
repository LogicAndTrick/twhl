@extends('app')

@section('content')
    @include('wiki.nav')
    <h2>Create New Page</h2>
    @form(wiki/create)
        @text(title $slug_title) = Page Title
        @textarea(content_text) = Page Content
        @submit = Create Page
    @endform
@endsection
