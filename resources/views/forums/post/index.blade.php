@extends('app')

@section('content')
    <hc>
        <h1>Forum Posts</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            @if ($user)
                <li><a href="{{ act('post', 'index') }}">Posts</a></li>
                <li class="active">Posted by @avatar($user inline)</li>
            @else
                <li class="active">Posts</li>
            @endif
        </ol>
        {!! $posts->render() !!}
    </hc>

    <ul class="media-list post-listing">
        @foreach ($posts as $post)
            <li class="media media-panel post" id="post-{{ $post->id }}">
                <div class="media-body">
                    <div class="media-heading">
                        <a href="{{ act('thread', 'locate-post', $post->id) }}">Post #{{ $post->id }}</a> &bull;
                        @date($post->created_at) &bull;
                        In <a href="{{ act('thread', 'view', $post->thread_id) }}">{{ $post->thread->title }}</a>
                        @if (permission('ForumAdmin'))
                            <a href="{{ act('post', 'delete', $post->id) }}" class="btn btn-xs btn-danger">
                                <span class="glyphicon glyphicon-remove"></span>
                                <span class="hidden-xs">Delete</span>
                            </a>
                        @endif
                        @if ($post->isEditable($post->thread))
                            <a href="{{ act('post', 'edit', $post->id) }}" class="btn btn-xs btn-primary">
                                <span class="glyphicon glyphicon-pencil"></span>
                                <span class="hidden-xs">Edit</span>
                            </a>
                        @endif
                    </div>
                    <div class="bbcode post-content">{!! $post->content_html !!}</div>
                </div>
                <div class="media-right">
                    <div class="media-object post-info">
                        @avatar($post->user full show_border=true)
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
    <div class="footer-container">
        {!! $posts->render() !!}
    </div>
@endsection