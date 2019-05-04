@title('Forum posts')
@extends('app')

@section('content')
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

    <div class="post-listing">
        @foreach ($posts as $post)
            {? $thread = $post->thread; ?}
            <div class="slot post" id="post-{{ $post->id }}">
                @if ($thread != null)
                    <div class="slot-heading">
                        <div class="slot-avatar hidden-md-up">
                            @avatar($post->user small show_name=false)
                        </div>
                        <div class="slot-title hidden-md-up">
                            @avatar($post->user text)
                            <div class="pull-right">
                                @if ($post->isEditable($post->thread))
                                    <a href="{{ act('post', 'edit', $post->id) }}" class="btn btn-xs btn-outline-primary">
                                        <span class="fa fa-pencil"></span>
                                        <span class="hidden-xs-down">Edit</span>
                                    </a>
                                @endif
                                @if (permission('ForumAdmin'))
                                    <a href="{{ act('post', 'delete', $post->id) }}" class="btn btn-xs btn-outline-danger">
                                        <span class="fa fa-remove"></span>
                                        <span class="hidden-xs-down">Delete</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="slot-subtitle">
                            Posted @date($post->created_at)
                            <br class="hidden-md-up" />
                            in <a href="{{ act('thread', 'view', $post->thread_id) }}">{{ $post->thread->title }}</a>
                            <a class="pull-right" href="{{ act('thread', 'locate-post', $post->id) }}">Post #{{ $post->id }}</a>
                        </div>
                    </div>
                    <div class="slot-row">
                        <div class="slot-main">
                            <div class="bbcode post-content">{!! $post->content_html !!}</div>
                        </div>
                        <div class="slot-right hidden-sm-down">
                            @avatar($post->user full)
                            @if ($post->isEditable($post->thread))
                                <a href="{{ act('post', 'edit', $post->id) }}" class="btn btn-xs btn-outline-primary">
                                    <span class="fa fa-pencil"></span>
                                    Edit
                                </a>
                            @endif
                            @if (permission('ForumAdmin'))
                                <a href="{{ act('post', 'delete', $post->id) }}" class="btn btn-xs btn-outline-danger">
                                    <span class="fa fa-remove"></span>
                                    Delete
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center m-2 font-italic">
                        This post was made on a thread that has been deleted.
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    <div class="footer-container">
        {!! $posts->render() !!}
    </div>
@endsection