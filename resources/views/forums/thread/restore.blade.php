@extends('app')

@section('content')
    @form(thread/restore)
        <h3>Restore Thread: {{ $forum->name }} / {{ $thread->title }}</h3>
        @hidden(id $thread)
        <p>Restoring this thread will make all posts in the thread visible again. Are you sure?</p>
        @submit
    @endform
@endsection
