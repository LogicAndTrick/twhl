@extends('app')

@section('content')
    <hc>
        <h1>Delete Post by @avatar($post->user inline)</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
            <li><a href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a></li>
            <li class="active">Delete Post</li>
        </ol>
    </hc>
    @form(post/delete)
        @hidden(id $post)
        <p>You are about to delete a post. Are you sure?</p>
        @submit = Delete Post
    @endform
@endsection
