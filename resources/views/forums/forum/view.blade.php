@title($forum->name)
@extends('app')

@section('content')

    <h1>
        <span class="forum-icon {{ $forum->getIconClasses() }}"></span>
        {{ $forum->name }}
        @if (permission('ForumCreate'))
            <a class="btn btn-outline-primary btn-xs" href="{{ act('thread', 'create', $forum->id) }}"><span class="fa fa-plus"></span> Create new thread</a>
        @endif
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
        <li class="active">Thread Listing</li>
    </ol>
    {!! $threads->render() !!}

    <table class="table table-striped thread-listing">
        <thead>
            <tr>
                <th class="col-icon"></th>
                <th class="col-topic">Topic</th>
                <th class="col-posts">Posts</th>
                <th class="col-last-post"><span class="hidden-xs">Last Post</span></th>
                @if (permission('ForumAdmin'))
                    <th class="col-mod"></th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($threads as $thread)
                <tr>
                    <td class="col-icon">
                        <a class="thread-title" href="{{ act('thread', 'view', $thread->id) }}?page=last">
                            <span class="forum-icon {{ $thread->getIconClasses() }}"></span>
                        </a>
                    </td>
                    <td class="col-topic">
                        <span class="hidden-md-up">
                            <a class="thread-title" href="{{ act('thread', 'view', $thread->id) }}?page=last">
                                <span class="forum-icon {{ $thread->getIconClasses() }}"></span>
                            </a>
                        </span>
                        <a class="thread-title" href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a><br/>
                        @avatar($thread->user text), @date($thread->created_at)
                        <div class="hidden-md-up">
                            <span class="posts">{{ $thread->stat_posts }} {{ ($thread->stat_posts == 1 ? 'reply' : 'replies') }}</span> &bull;
                            <span class="views">{{ $thread->stat_views }} {{ ($thread->stat_views == 1 ? 'view' : 'views') }}</span>
                        </div>
                    </td>
                    <td class="col-posts">
                        <span class="posts">{{ $thread->stat_posts }} {{ ($thread->stat_posts == 1 ? 'reply' : 'replies') }}</span><br/>
                        <span class="views">{{ $thread->stat_views }} {{ ($thread->stat_views == 1 ? 'view' : 'views') }}</span>
                    </td>
                    <td class="col-last-post">
                        @if ($thread->last_post)
                            @avatar($thread->last_post->user small show_name=false)
                            @avatar($thread->last_post->user text)<br/>
                            <a href="{{ act('thread', 'view', $thread->id) }}?page=last#post-{{ $thread->last_post->id }}">{{ $thread->last_post->created_at->diffForHumans() }}</a>
                        @endif
                    </td>
                    @if (permission('ForumAdmin'))
                        <td class="col-mod">
                            @if ($thread->deleted_at)
                                <a href="{{ act('thread', 'restore', $thread->id) }}" class="btn btn-xs btn-outline-info"><span class="fa fa-repeat"></span></a>
                            @else
                                <a href="{{ act('thread', 'edit', $thread->id) }}" class="btn btn-xs btn-outline-primary"><span class="fa fa-pencil"></span></a>
                                <a href="{{ act('thread', 'delete', $thread->id) }}" class="btn btn-xs btn-outline-danger"><span class="fa fa-remove"></span></a>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer-container">
        {!! $threads->render() !!}
    </div>
@endsection
