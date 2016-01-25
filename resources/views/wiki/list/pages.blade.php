@title('Wiki Pages')
@extends('app')

@section('content')
    @include('wiki.nav')
    <hc>
        <h1>Wiki Pages</h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/wiki') }}">Wiki</a></li>
            <li class="active">Page List</li>
        </ol>
        {!! $revisions->render() !!}
    </hc>

    <ul>
    @foreach ($revisions as $r)
        <li><a href="{{ act('wiki', 'page', $r->slug) }}">{{ $r->getNiceTitle() }}</a></li>
    @endforeach
    </ul>

@endsection