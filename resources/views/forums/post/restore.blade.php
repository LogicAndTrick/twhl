@extends('app')

@section('content')
    <hc>
        <h1>Restore Post by @include('user._avatar', [ 'class' => 'inline', 'user' => $post->user ])</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
            <li><a href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a></li>
            <li class="active">Restore Post</li>
        </ol>
    </hc>
    @form(post/restore)
        @hidden(id $post)
        <p>Restoring this post will make it visible again. Are you sure?</p>
        @submit = Restore Post
    @endform
@endsection
