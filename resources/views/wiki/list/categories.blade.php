@extends('app')

@section('content')
    @include('wiki.nav')
    <h2>Wiki Categories</h2>

    <ul>
    @foreach ($categories as $c)
        <li><a href="{{ act('wiki', 'page', 'category:'.$c->value) }}">{{ $c->value }}</a></li>
    @endforeach
    </ul>

    {!! $categories->render() !!}
@endsection