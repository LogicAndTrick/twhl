@extends('app')

@section('content')
    @form(thread/delete)
        <h3>Delete Thread: {{ $forum->name }} / {{ $thread->title }}</h3>
        @hidden(id $thread)
        <p>You are about to delete a thread, making all the posts inaccessible. Are you sure?</p>
        @submit
    @endform
@endsection
