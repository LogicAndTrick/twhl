@extends('app')

@section('content')
    <hc>
        <h1>Delete Thread: {{ $thread->title }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
            <li><a href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a></li>
            <li class="active">Delete Thread</li>
        </ol>
    </hc>
    @form(thread/delete)
        @hidden(id $thread)
        <p>You are about to delete a thread, making all the posts inaccessible. Are you sure?</p>
        @submit = Delete Thread
    @endform
@endsection
