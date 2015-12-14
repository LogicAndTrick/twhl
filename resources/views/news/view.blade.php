@extends('app')

@section('content')
    <hc>
        @if (permission('NewsAdmin'))
            <a href="{{ act('news', 'delete', $news->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Delete</a>
            <a href="{{ act('news', 'edit', $news->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
        @endif
        <h1>{{ $news->title }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('news', 'index') }}">News</a></li>
            <li class="active">View News Post</li>
        </ol>
    </hc>
    <div class="bbcode">
        {!! $news->content_html !!}
    </div>
    @include('comments.list', [ 'article' => $news, 'comments' => $comments, 'article_type' => \App\Models\Comments\Comment::NEWS, 'article_id' => $news->id ])
@endsection