@title('Edit news post: '.$news->title)
@extends('app')

@section('content')
    <h1>Edit news post: {{ $news->title }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('news', 'index') }}">News</a></li>
        <li><a href="{{ act('news', 'view', $news->id) }}">{{ $news->title }}</a></li>
        <li class="active">Edit</li>
    </ol>

    @form(news/edit)
        @hidden(id $news)
        @text(title $news) = News Post Title
        <div class="wikicode-input">
            @textarea(content_text:text $news) = News Post Content
        </div>
        @submit = Edit News Post
    @endform
@endsection
