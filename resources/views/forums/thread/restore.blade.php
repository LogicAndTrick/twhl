@extends('app')

@section('content')
    <hc>
        <h1>Restore Thread: {{ $thread->title }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
            <li><a href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a></li>
            <li class="active">Restore Thread</li>
        </ol>
    </hc>
    @form(thread/restore)
        @hidden(id $thread)
        <p>Restoring this thread will make all posts in the thread visible again. Are you sure?</p>
        @submit = Restore Thread
    @endform
@endsection
