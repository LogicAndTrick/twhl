@title('Forum posts')
@extends('app')

@section('content')
    <hc>
        <h1>Forum posts</h1>
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
                        <h3 class="visible-xs-block">
                            @avatar($post->user inline)
                            @if ($post->isEditable($post->thread))
                                <a href="{{ act('post', 'edit', $post->id) }}" class="btn btn-xs btn-primary">
                                    <span class="fa fa-pencil"></span>
                                    <span class="hidden-xs">Edit</span>
                                </a>
                            @endif
                            @if (permission('ForumAdmin'))
                                <a href="{{ act('post', 'delete', $post->id) }}" class="btn btn-xs btn-danger">
                                    <span class="fa fa-remove"></span>
                                    <span class="hidden-xs">Delete</span>
                                </a>
                            @endif
                        </h3>
                        <a class="pull-right" href="{{ act('thread', 'locate-post', $post->id) }}">Post #{{ $post->id }}</a>
                        Posted @date($post->created_at)
                        <br class="visible-xs-block"/>
                        in <a href="{{ act('thread', 'view', $post->thread_id) }}">{{ $post->thread->title }}</a>
                    </div>
                    <div class="bbcode post-content">{!! $post->content_html !!}</div>
                </div>
                <div class="media-right">
                    <div class="media-object post-info">
                        @avatar($post->user full show_border=false)
                        @if ($post->isEditable($post->thread))
                            <a href="{{ act('post', 'edit', $post->id) }}" class="btn btn-xs btn-primary">
                                <span class="fa fa-pencil"></span>
                                <span class="hidden-xs">Edit</span>
                            </a>
                        @endif
                        @if (permission('ForumAdmin'))
                            <a href="{{ act('post', 'delete', $post->id) }}" class="btn btn-xs btn-danger">
                                <span class="fa fa-remove"></span>
                                <span class="hidden-xs">Delete</span>
                            </a>
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
    <div class="footer-container">
        {!! $posts->render() !!}
    </div>
@endsection