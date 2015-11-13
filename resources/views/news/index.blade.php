@extends('app')

@section('content')
    <hc>
        @if (permission('NewsAdmin'))
            <a class="btn btn-primary btn-xs" href="{{ act('news', 'create') }}"><span class="glyphicon glyphicon-plus"></span> Create new news post</a>
        @endif
        <h1>News Posts</h1>
        {!! $newses->render() !!}
    </hc>
    @foreach ($newses as $news)
        <h2><a href="{{ act('news', 'view', $news->id) }}">{{ $news->title }}</a></h2>
        <div class="bbcode">{!! $news->content_html !!}</div>
    @endforeach
@endsection