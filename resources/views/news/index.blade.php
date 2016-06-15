@title('News posts')
@extends('app')

@section('content')
    <hc>
        @if (permission('NewsAdmin'))
            <a class="btn btn-primary btn-xs" href="{{ act('news', 'create') }}"><span class="glyphicon glyphicon-plus"></span> Create new news post</a>
        @endif
        <h1>News posts</h1>
        {!! $newses->render() !!}
    </hc>
    <ul class="media-list">
        @foreach ($newses as $news)
            <li class="media media-panel" id="news-{{ $news->id }}">
              <div class="media-left">
                <div class="media-object">
                    @avatar($news->user small show_border=false show_name=false)
                </div>
              </div>
              <div class="media-body">
                <div class="media-heading">
                    @if (permission('NewsAdmin'))
                        <a href="{{ act('news', 'delete', $news->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Delete</a>
                        <a href="{{ act('news', 'edit', $news->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
                    @endif
                    <h2><a href="{{ act('news', 'view', $news->id) }}">{{ $news->title }}</a></h2>
                    <span class="visible-xs-inline">@avatar($news->user inline)</span><span class="hidden-xs">@avatar($news->user text)</span> &bull;
                    @date($news->created_at)
                </div>
                <div class="bbcode">{!! $news->content_html !!}</div>
                <div class="media-footer">
                    <a href="{{ act('news', 'view', $news->id) }}" class="btn btn-xs btn-link link">
                        <span class="glyphicon glyphicon-comment"></span>
                        {{ $news->stat_comments }} comment{{$news->stat_comments==1?'':'s'}}
                    </a>
                </div>
              </div>
            </li>
        @endforeach
    </ul>
    <div class="footer-container">
        {!! $newses->render() !!}
    </div>
@endsection