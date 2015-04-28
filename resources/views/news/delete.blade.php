@extends('app')

@section('content')
    @form(news/delete)
        <h3>Delete News Post: {{ $news->title }}</h3>
        <p>Are you sure you want to delete this news post?</p>
        @hidden(id $news)
        @submit
    @endform
@endsection