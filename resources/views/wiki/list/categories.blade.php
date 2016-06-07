@title('Wiki categories')
@extends('app')

@section('content')
    @include('wiki.nav')
    <hc>
        <h1>Wiki categories</h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/wiki') }}">Wiki</a></li>
            <li class="active">Category List</li>
        </ol>
        {!! $categories->render() !!}
    </hc>

    <ul>
    @foreach ($categories as $c)
        <li><a href="{{ act('wiki', 'page', 'category:'.$c->value) }}">Category: {{ $c->title }}</a></li>
    @endforeach
    </ul>

@endsection