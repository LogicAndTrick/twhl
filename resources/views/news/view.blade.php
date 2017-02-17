@title('News post: '.$news->title)
@extends('app')

@section('content')
    <h1>
        {{ $news->title }}
        @if (permission('NewsAdmin'))
            <a href="{{ act('news', 'delete', $news->id) }}" class="btn btn-outline-danger btn-xs"><span class="fa fa-remove"></span></a>
            <a href="{{ act('news', 'edit', $news->id) }}" class="btn btn-outline-primary btn-xs"><span class="fa fa-pencil"></span></a>
        @endif
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('news', 'index') }}">News</a></li>
        <li class="active">View News Post</li>
    </ol>

    <div class="slot">
        <div class="slot-heading">
            <div class="slot-avatar hidden-md-up">
                @avatar($news->user small show_name=false)
            </div>
            <div class="slot-title hidden-md-up">
                @avatar($news->user text)
            </div>
            <div class="slot-subtitle">
                Posted @date($news->created_at)
            </div>
        </div>
        <div class="slot-row">
            <div class="slot-left hidden-sm-down">
                @avatar($news->user full)
            </div>
            <div class="slot-main">
                <div class="bbcode">{!! $news->content_html !!}</div>
            </div>
        </div>
    </div>

    @include('comments.list', [ 'article' => $news, 'comments' => $comments, 'article_type' => \App\Models\Comments\Comment::NEWS, 'article_id' => $news->id ])
@endsection