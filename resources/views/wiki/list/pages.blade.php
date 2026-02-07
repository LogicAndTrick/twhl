@title('Wiki pages')
@extends('app')

@section('content')
    @include('wiki.nav')

    <h1>
        <span class="fa fa-files-o"></span>
        Wiki pages
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('wiki', 'index') }}">Wiki</a></li>
        <li class="active">Page List</li>
    </ol>

    {!! $revisions->render() !!}

    <ul>
    @foreach ($revisions as $r)
        <li><a href="{{ act('wiki', 'page', $r->slug) }}">{{ $r->getNiceTitle() }}</a></li>
    @endforeach
    </ul>

@endsection