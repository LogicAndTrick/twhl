@extends('app')

@section('content')
    @form(forum/delete)
        <h3>Delete Forum: {{ $forum->name }}</h3>
        @hidden(id $forum)
        <p>Deleting a forum is probably not a great idea. Are you sure?</p>
        @submit
    @endform
@endsection
