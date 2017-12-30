@title('Create news post')
@extends('app')

@section('content')
    <h1>Create news post</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('news', 'index') }}">News</a></li>
        <li class="active">Create News</li>
    </ol>

    @form(news/create)
        @text(title) = News Post Title
        <div class="wikicode-input">
            @textarea(text) = News Post Content
        </div>
        @submit = Create News Post
    @endform
@endsection
