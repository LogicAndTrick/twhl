@title('News posts')
@extends('app')

@section('content')
    <h1>
        <span class="fa fa-newspaper-o"></span>
        News posts
        @if (permission('NewsAdmin'))
            <a class="btn btn-primary btn-xs" href="{{ act('news', 'create') }}"><span class="fa fa-plus"></span> Create new news post</a>
        @endif
    </h1>

    {!! $newses->render() !!}

    <div class="news-list">
        @foreach ($newses as $news)
            <div class="slot" id="news-{{ $news->id }}">
                <div class="slot-heading">
                    <div class="slot-avatar">
                        @avatar($news->user small show_name=false)
                    </div>
                    <div class="slot-title">
                        <a href="{{ act('news', 'view', $news->id) }}">{{ $news->title }}</a>
                        @if (permission('NewsAdmin'))
                            <a href="{{ act('news', 'delete', $news->id) }}" class="btn btn-outline-danger btn-xs"><span class="fa fa-remove"></span> Delete</a>
                            <a href="{{ act('news', 'edit', $news->id) }}" class="btn btn-outline-primary btn-xs"><span class="fa fa-pencil"></span> Edit</a>
                        @endif
                    </div>
                    <div class="slot-subtitle">
                        @avatar($news->user text) &bull;
                        @date($news->created_at) &bull;
                        <a href="{{ act('news', 'view', $news->id) }}">
                            <span class="fa fa-comment"></span>
                            {{ $news->stat_comments }} comment{{$news->stat_comments==1?'':'s'}}
                        </a>
                    </div>
                </div>
                <div class="slot-main">
                    <div class="bbcode">{!! $news->content_html !!}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="footer-container">
        {!! $newses->render() !!}
    </div>
@endsection