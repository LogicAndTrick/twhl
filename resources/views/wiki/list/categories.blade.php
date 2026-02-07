@title('Wiki categories')
@extends('app')

@section('content')
    @include('wiki.nav')

    <h1>
        <span class="fa fa-list-ul"></span>
        Wiki categories
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('wiki', 'index') }}">Wiki</a></li>
        <li class="active">Category List</li>
    </ol>

    {!! $categories->render() !!}

    <ul>
    @foreach ($categories as $c)
        <li><a href="{{ act('wiki', 'page', 'category:'.$c->value) }}">Category: {{ $c->title }}</a></li>
    @endforeach
    </ul>

@endsection