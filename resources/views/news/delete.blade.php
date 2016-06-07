@title('Delete news post: '.$news->title)
@extends('app')

@section('content')
    <hc>
        <h1>Delete news post: {{ $news->title }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('news', 'index') }}">News</a></li>
            <li><a href="{{ act('news', 'view', $news->id) }}">{{ $news->title }}</a></li>
            <li class="active">Delete</li>
        </ol>
    </hc>
    @form(news/delete)
        <p>Are you sure you want to delete this news post?</p>
        @hidden(id $news)
        @submit = Delete News Post
    @endform
@endsection