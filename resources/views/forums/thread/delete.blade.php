@title('Delete forum thread: '.$thread->title)
@extends('app')

@section('content')
    <h1>Delete thread: {{ $thread->title }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
        <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
        <li><a href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a></li>
        <li class="active">Delete Thread</li>
    </ol>

    @form(thread/delete)
        @hidden(id $thread)
        <p>You are about to delete a thread, making all the posts inaccessible. Are you sure?</p>
        @submit = Delete Thread
    @endform
@endsection
