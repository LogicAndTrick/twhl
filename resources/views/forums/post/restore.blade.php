@title('Restore forum post by '.$post->user->name)
@extends('app')

@section('content')
    <h1>Restore post by @avatar($post->user inline)</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
        <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
        <li><a href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a></li>
        <li class="active">Restore Post</li>
    </ol>

    @form(post/restore)
        @hidden(id $post)
        <p>Restoring this post will make it visible again. Are you sure?</p>
        <div class="card card-body">
            <div class="bbcode {{$post->user->getClasses()}}">{!! $post->content_html !!}</div>
        </div>
        @submit = Restore Post
    @endform
@endsection
