@extends('app')

@section('content')
    @form(thread/edit)
        <h3>Edit Thread: {{ $forum->name }} / {{ $thread->title }}</h3>
        @hidden(id $thread)
        @text(title $thread) = Title
        @autocomplete(forum_id api/forums $thread) = Forum
        @autocomplete(user_id api/users $thread) = Thread Creator
        @checkbox(is_open $thread) = Thread is open
        @checkbox(is_sticky $thread) = Thread is sticky
        @submit
    @endform
@endsection
