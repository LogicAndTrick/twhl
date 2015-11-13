@extends('app')

@section('content')
    <hc>
        <h1>Edit Thread: {{ $thread->title }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
            <li><a href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a></li>
            <li class="active">Edit Thread</li>
        </ol>
    </hc>
    @form(thread/edit)
        @hidden(id $thread)
        @text(title $thread) = Title
        @autocomplete(forum_id api/forums $thread) = Forum
        @autocomplete(user_id api/users $thread) = Thread Creator
        @checkbox(is_open $thread) = Thread is open
        @checkbox(is_sticky $thread) = Thread is sticky
        @submit = Edit Thread
    @endform
@endsection
