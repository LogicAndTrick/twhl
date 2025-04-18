@title($thread->title)
@extends('app')

<?php
    if ($posts->onFirstPage() && $posts->count() > 0) $meta_description = $posts[0]->content_text;
?>

@section('content')

    <h1>
        {{ $thread->title }}

        @if (permission('ForumAdmin'))
            @if ($thread->deleted_at)
                <a href="{{ act('thread', 'restore', $thread->id) }}" class="btn btn-xs btn-outline-info"><span class="fa fa-repeat"></span></a>
            @else
                <a href="{{ act('thread', 'delete', $thread->id) }}" class="btn btn-xs btn-outline-danger"><span class="fa fa-remove"></span></a>
                <a href="{{ act('thread', 'edit', $thread->id) }}" class="btn btn-xs btn-outline-primary"><span class="fa fa-pencil"></span></a>
            @endif
        @endif
        <small class="pull-right hidden-sm-down">Created @date($thread->created_at) by @avatar($thread->user inline)</small>
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
        <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
        <li class="active">View Thread</li>
        <li class="ms-auto no-breadcrumb">
            @if (Auth::check())
                @if ($subscription)
                    <a href="{{ act('thread', 'unsubscribe', $thread->id) }}" class="btn btn-xs btn-outline-inverse"><span class="fa fa-bell"></span> Unsubscribe</a>
                @else
                    <a href="{{ act('thread', 'subscribe', $thread->id) }}" class="btn btn-xs btn-outline-inverse"><span class="fa fa-bell"></span> Subscribe</a>
                @endif
            @endif
        </li>
    </ol>

    {!! $posts->render() !!}

    <p class="hidden-md-up">Created @date($thread->created_at) by @avatar($thread->user inline)</p>

    <div class="post-listing">
        @foreach ($posts as $post)
            <div class="slot post" id="post-{{ $post->id }}">
                <div class="slot-heading">
                    <div class="slot-avatar hidden-md-up">
                        @avatar($post->user small show_name=false)
                    </div>
                    <div class="slot-title hidden-md-up">
                        @avatar($post->user text)
                        <div class="pull-right">
                            @if ($post->isEditable($thread))
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
                        <a class="pull-right" href="{{ act('thread', 'locate-post', $post->id) }}">Post #{{ $post->id }}</a>
                    </div>
                </div>
                <div class="slot-row">
                    <div class="slot-main">
                        <div class="bbcode post-content {{$post->user->getClasses()}}">{!! $post->content_html !!}</div>
                    </div>
                    <div class="slot-right hidden-sm-down">
                        @avatar($post->user full)
                        @if ($post->isEditable($thread))
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
            </div>
        @endforeach
    </div>
    <div class="footer-container">
        {!! $posts->render() !!}
    </div>

    @if (!$thread->isPostable())
        <div class="card card-body">
            {{ $thread->getUnpostableReason() }}
        </div>
    @else
        @if (Date::DiffDays(Date::Now(), $thread->last_post ? $thread->last_post->updated_at : $thread->updated_at) > \App\Models\Forums\ForumThread::THREAD_LOCK_DAYS)
            <div class="alert alert-warning">
                Careful! This thread is over 90 days old, and bumping it will cause it to become postable again.
            </div>
        @endif
        @if (!$thread->is_open)
            <div class="alert alert-warning">
                This thread is closed, regular users are not able to post in it.
            </div>
        @endif
        @form(post/create)
            <h3>Post a Reply</h3>
            <input type="hidden" name="thread_id" value="{{ $thread->id }}" />
            <div class="wikicode-input">
                @textarea(text) = Post Content
            </div>
            @submit
        @endform
    @endif
@endsection
