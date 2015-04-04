@extends('app')

@section('content')

    <h2>Forum: {{ $forum->name }}</h2>

    {!! $threads->render() !!}
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
                        <i class="glyphicon glyphicon-twhl icon-twhl-{$cls}{$col}"></i> <a href="{{ act('thread', 'view', $thread->id, 'last') }}">{{ $thread->title }}</a><br>
                        by <a href="#">{{ $thread->user->name }}</a>,
                        {{ Date::TimeAgo($thread->created_at) }}
                    </td>
                    <td>{{ $thread->stat_posts }}</td>
                    <td>{{ $thread->stat_views }}</td>
                    <td class="last-post">
                        @if ($thread->last_post)
                            <a href="{{ act('thread', 'view', $thread->id, 'last') }}#post-{{ $thread->last_post->id }}">{{ Date::TimeAgo($thread->last_post->updated_at) }}</a><br>
                            by <a href="#">{{ $thread->last_post->user->name }}</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {!! $threads->render() !!}

@endsection
