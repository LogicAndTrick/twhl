@title('News post: '.$news->title)
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
    <div>
        <div class="media media-panel">
            <div class="media-left">
                <div class="media-object">
                    @avatar($news->user small show_border=false show_name=false)
                </div>
            </div>
            <div class="media-body">
                <div class="media-heading">
                    @avatar($news->user text) &bull;
                    @date($news->created_at)
                </div>
                <div class="bbcode">
                    {!! $news->content_html !!}
                </div>
            </div>
        </div>
    </div>
    @include('comments.list', [ 'article' => $news, 'comments' => $comments, 'article_type' => \App\Models\Comments\Comment::NEWS, 'article_id' => $news->id ])
@endsection