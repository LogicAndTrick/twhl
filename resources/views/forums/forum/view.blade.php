@extends('app')

@section('content')

    <hc>
        @if (permission('ForumCreate'))
            <a class="btn btn-primary btn-xs" href="{{ act('thread', 'create', $forum->id) }}"><span class="glyphicon glyphicon-plus"></span> Create new thread</a>
        @endif
        <h1>Forum: {{ $forum->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            <li class="active">Thread Listing</li>
        </ol>
        {!! $threads->render() !!}
    </hc>

    <table class="table table-striped thread-listing">
        <thead>
            <tr>
                <th>Topic</th>
                <th>Posts</th>
                <th>Views</th>
                <th class="last-post">Last Post</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($threads as $thread)
                <tr>
                    <td>
                        <a href="{{ act('thread', 'view', $thread->id) }}?page=last">{{ $thread->title }}</a><br>
                        by @avatar($thread->user inline),
                        @date($thread->created_at)
                    </td>
                    <td>{{ $thread->stat_posts }}</td>
                    <td>{{ $thread->stat_views }}</td>
                    <td class="last-post">
                        @if ($thread->last_post)
                            <a href="{{ act('thread', 'view', $thread->id) }}?page=last#post-{{ $thread->last_post->id }}">{{ $thread->last_post->created_at->diffForHumans() }}</a><br>
                            by @avatar($thread->last_post->user inline)
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection
