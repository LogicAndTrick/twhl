@title('Create forum thread: '.$forum->name)
@extends('app')

@section('content')
    <h1>Create thread: {{ $forum->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
        <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
        <li class="active">Create Thread</li>
    </ol>

    @form(thread/create)
        <input type="hidden" name="forum_id" value="{{ $forum->id }}" />
        @text(title) = Thread Title
        <div class="wikicode-input">
            @textarea(text) = Post Content
        </div>
        @submit = Create Thread
    @endform
@endsection
