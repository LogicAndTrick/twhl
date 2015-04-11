@extends('app')

@section('content')
    @include('wiki.nav')
    <h2>Wiki Pages</h2>

    <ul>
    @foreach ($revisions as $r)
        <li><a href="{{ act('wiki', 'page', $r->slug) }}">{{ $r->title }}</a></li>
    @endforeach
    </ul>

    {!! $revisions->render() !!}
@endsection