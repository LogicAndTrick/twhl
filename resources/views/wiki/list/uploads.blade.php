@title('Wiki uploads')
@extends('app')

@section('content')
    @include('wiki.nav')

    <h1>Wiki uploads</h1>

    <ol class="breadcrumb">
        <li><a href="{{ url('/wiki') }}">Wiki</a></li>
        <li class="active">Upload List</li>
    </ol>

    {!! $revisions->render() !!}

    <ul>
    @foreach ($revisions as $r)
        <li><a href="{{ act('wiki', 'page', $r->slug) }}">{{ $r->getNiceTitle() }}</a></li>
    @endforeach
    </ul>

@endsection