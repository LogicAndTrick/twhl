@title($title)
@extends('app')

@section('content')
    @include('wiki.nav')

    <h1>
        <span class="fa fa-star"></span>
        {{ $title }}
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ url('/wiki') }}">Wiki</a></li>
        <li><a href="{{ url('/wiki-special') }}">Special Pages</a></li>
        <li class="active">{{ $title }}</li>
    </ol>

    @foreach ($sections as $s)
        <h4>{{ $s['title'] }}</h4>
        @include('wiki.special.'.$s['type'], $s)
    @endforeach


@endsection