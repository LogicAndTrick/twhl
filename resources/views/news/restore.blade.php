@title('Restore news post: '.$news->title)
@extends('app')

@section('content')
    <h1>Restore news post: {{ $news->title }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('news', 'index') }}">News</a></li>
        <li><a href="{{ act('news', 'view', $news->id) }}">{{ $news->title }}</a></li>
        <li class="active">Restore</li>
    </ol>

    @form(news/restore)
        <p>Are you sure you want to restore this news post?</p>
        @hidden(id $news)
        @submit = Restore News Post
    @endform
@endsection