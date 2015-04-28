@extends('app')

@section('content')
    @if (permission('NewsAdmin'))
        <p>
            <a href="{{ act('news', 'create') }}">Create new news post</a>
        </p>
    @endif
    <h2>News Posts</h2>
    @foreach ($newses as $news)
        <h2><a href="{{ act('news', 'view', $news->id) }}">{{ $news->title }}</a></h2>
        <div class="bbcode">{!! $news->content_html !!}</div>
    @endforeach
    {!! $newses->render() !!}
@endsection