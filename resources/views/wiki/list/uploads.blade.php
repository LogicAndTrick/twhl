@extends('app')

@section('content')
    @include('wiki.nav')
    <hc>
        <h1>Wiki Uploads</h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/wiki') }}">Wiki</a></li>
            <li class="active">Upload List</li>
        </ol>
        {!! $revisions->render() !!}
    </hc>

    <ul>
    @foreach ($revisions as $r)
        <li><a href="{{ act('wiki', 'page', $r->slug) }}">{{ $r->title }}</a></li>
    @endforeach
    </ul>

@endsection