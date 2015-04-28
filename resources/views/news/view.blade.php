@extends('app')

@section('content')
    <h2>
        {{ $news->title }}
        @if (permission('NewsAdmin'))
            <a href="{{ act('news', 'delete', $news->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Delete</a>
            <a href="{{ act('news', 'edit', $news->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
        @endif
    </h2>
    <div class="bbcode">
        {!! $news->content_html !!}
    </div>
    @include('comments.list', [ 'comments' => $comments, 'article_type' => \App\Models\Comments\Comment::NEWS, 'article_id' => $news->id ])
@endsection