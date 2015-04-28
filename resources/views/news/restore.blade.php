@extends('app')

@section('content')
    @form(news/restore)
        <h3>Restore News Post: {{ $news->title }}</h3>
        <p>Are you sure you want to restore this news post?</p>
        @hidden(id $news)
        @submit
    @endform
@endsection