@title('Delete forum post by '.$post->user->name)
@extends('app')

@section('content')
    <h1>Delete post by @avatar($post->user inline)</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
        <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
        <li><a href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a></li>
        <li class="active">Delete Post</li>
    </ol>

    @form(post/delete)
        @hidden(id $post)
        <p>Are you sure you want to delete this post?</p>
        <div class="form-group">
            <div class="card card-body">
                <div class="bbcode {{$post->user->getClasses()}}">{!! $post->content_html !!}</div>
            </div>
        </div>
        @submit = Delete Post
    @endform
@endsection
