@title('Edit forum post by '.$post->user->name)
@extends('app')

@section('content')
    <h1>Edit post by @avatar($post->user inline)</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
        <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
        <li><a href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a></li>
        <li class="active">Edit Post</li>
    </ol>

    @form(post/edit)
        @hidden(id $post)
        @if (permission('ForumAdmin'))
            @autocomplete(user_id api/users $post) = Post Owner
        @endif
        <div class="wikicode-input">
            @textarea(content_text $post) = Post Content
        </div>
        @submit = Edit Post
    @endform
@endsection
