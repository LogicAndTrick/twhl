@title('Forum: '.$forum->name)
@extends('app')

@section('content')

    <hc>
        @if (permission('ForumCreate'))
            <a class="btn btn-primary btn-xs" href="{{ act('thread', 'create', $forum->id) }}"><span class="glyphicon glyphicon-plus"></span> Create new thread</a>
        @endif
        <h1>{{ $forum->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            <li class="active">Thread Listing</li>
        </ol>
        {!! $threads->render() !!}
    </hc>

    <table class="table table-striped thread-listing">
        <thead>
            <tr>
                <th class="col-icon"></th>
                <th class="col-topic">Topic</th>
                <th class="col-posts">Posts</th>
                <th class="col-last-post">Last Post</th>
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
                        <a class="thread-title" href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a><br/>
                        @avatar($thread->user text), @date($thread->created_at)
                    </td>
                    <td class="col-posts">
                        {{ $thread->stat_posts }} {{ ($thread->stat_posts == 1 ? 'reply' : 'replies') }}<br/>
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
                                <a href="{{ act('thread', 'restore', $thread->id) }}" class="btn btn-xs btn-info"><span class="glyphicon glyphicon-repeat"></span></a>
                            @else
                                <a href="{{ act('thread', 'edit', $thread->id) }}" class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-pencil"></span></a>
                                <a href="{{ act('thread', 'delete', $thread->id) }}" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-remove"></span></a>
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
