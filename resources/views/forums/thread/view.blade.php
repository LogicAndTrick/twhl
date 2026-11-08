@title($thread->title)
@extends('app')

<?php
    if ($posts->onFirstPage() && $posts->count() > 0) $meta_description = $posts[0]->content_text;
?>

@section('content')

    <h1 class="d-flex align-items-center">
        <span class="me-auto">{{ $thread->title }}</span>
        <small class="hidden-sm-down">Created @date($thread->created_at) by @avatar($thread->user inline)</small>
        @if (permission('ForumAdmin'))
            @if ($thread->deleted_at)
                <a href="{{ act('thread', 'restore', $thread->id) }}" class="btn btn-xs btn-outline-info"><span class="fas fa-arrow-rotate-right"></span></a>
            @else
                <a href="{{ act('thread', 'delete', $thread->id) }}" class="btn btn-xs btn-outline-danger"><span class="fas fa-xmark"></span></a>
                <a href="{{ act('thread', 'edit', $thread->id) }}" class="btn btn-xs btn-outline-primary"><span class="fas fa-pencil"></span></a>
            @endif
        @endif
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
        <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
        <li class="active">View Thread</li>
        <li class="ms-auto no-breadcrumb">
            @if (Auth::check())
                @if ($subscription)
                    @form(thread/unsubscribe)
                        @hidden(id $thread->id)
                        <button type="submit" class="btn btn-xs btn-outline-inverse"><span class="fas fa-bell"></span> Unsubscribe</button>
                    @endform
                @else
                    @form(thread/subscribe)
                        @hidden(id $thread->id)
                        <button type="submit" class="btn btn-xs btn-outline-inverse"><span class="fas fa-bell"></span> Subscribe</button>
                    @endform
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
                        <div class="float-end">
                            @if ($post->isEditable($thread))
                                <a href="{{ act('post', 'edit', $post->id) }}" class="btn btn-xs btn-outline-primary">
                                    <span class="fas fa-pencil"></span>
                                    <span class="hidden-xs-down">Edit</span>
                                </a>
                            @endif
                            @if (permission('ForumAdmin'))
                                <a href="{{ act('post', 'delete', $post->id) }}" class="btn btn-xs btn-outline-danger">
                                    <span class="fas fa-xmark"></span>
                                    <span class="hidden-xs-down">Delete</span>
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="slot-subtitle">
                        Posted @date($post->created_at)
                        @auth
                            <a class="float-end" href="{{ act('thread', 'locate-post', $post->id) }}">Post #{{ $post->id }}</a>
                        @endauth
                    </div>
                </div>
                <div class="slot-row">
                    <div class="slot-main">
                        <div class="bbcode post-content {{$post->user->getClasses()}}">{!! $post->content_html !!}</div>
                        @include('reactions.list', [ 'type' => App\Models\Reactions\Reaction::FORUM_POST, 'item' => $post ])
                    </div>
                    <div class="slot-right hidden-sm-down">
                        @avatar($post->user full)
                        @if ($post->isEditable($thread))
                            <a href="{{ act('post', 'edit', $post->id) }}" class="btn btn-xs btn-outline-primary">
                                <span class="fas fa-pencil"></span>
                                Edit
                            </a>
                        @endif
                        @if (permission('ForumAdmin'))
                            <a href="{{ act('post', 'delete', $post->id) }}" class="btn btn-xs btn-outline-danger">
                                <span class="fas fa-xmark"></span>
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
