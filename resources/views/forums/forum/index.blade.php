@title('Forum listings')
@extends('app')

@section('content')
    <h1>
        <span class="fa fa-comments"></span> Forum listing
        @if (permission('ForumAdmin'))
            @if ($show_deleted)
                <a class="btn btn-outline-warning btn-xs" href="{{ act('forum', 'index') }}"><span class="fa fa-eye-slash"></span> Hide deleted forums</a>
            @else
                <a class="btn btn-outline-warning btn-xs" href="{{ act('forum', 'index') }}?deleted"><span class="fa fa-eye"></span> Show deleted forums</a>
            @endif
            <a class="btn btn-outline-primary btn-xs" href="{{ act('forum', 'create') }}"><span class="fa fa-plus"></span> Create new forum</a>
        @endif
    </h1>
    <div class="forum-listing">
        @foreach ($forums as $forum)
            <div class="slot {{ $forum->deleted_at ? 'inactive' : '' }}">
                <div class="slot-heading">
                    <h2 class="slot-title">
                        @if (permission('ForumAdmin'))
                            @if ($forum->deleted_at)
                                <a href="{{ act('forum', 'restore', $forum->id) }}" class="btn btn-xs btn-outline-info"><span class="fa fa-repeat"></span></a>
                            @else
                                <a href="{{ act('forum', 'delete', $forum->id) }}" class="btn btn-xs btn-outline-danger"><span class="fa fa-remove"></span></a>
                                <a href="{{ act('forum', 'edit', $forum->id) }}" class="btn btn-xs btn-outline-primary"><span class="fa fa-pencil"></span></a>
                            @endif
                        @endif
                        <a href="{{ act('forum', 'view', $forum->slug) }}">
                            <span class="forum-icon {{ $forum->getIconClasses() }}"></span>
                            {{ $forum->name }}
                        </a>
                        <small class="pull-right">{{ $forum->stat_posts }} posts in {{ $forum->stat_threads }} threads</small>
                    </h2>
                </div>
                <div class="slot-main">
                    <div class="row">
                        <div class="col-lg-4">
                            <p class="description">
                                {{ $forum->description }}
                            </p>
                        </div>
                        <div class="col-lg-8 forum-info">
                            <table class="table table-sm recent-forum-threads">
                                <thead>
                                    <tr>
                                        <th class="col-thread">Recently Active Threads <a class="see-all pull-right btn btn-xs btn-outline-primary" href="{{ act('forum', 'view', $forum->slug) }}">See all</a></th>
                                        <th class="col-time">Last Post</th>
                                        <th class="col-user">By User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recent_threads->where('forum_id', $forum->id)->sortBy('updated_at', 0, true) as $thread)
                                        <tr>
                                            <td class="col-thread">
                                                <a href="{{ act('thread', 'view', $thread->id) }}?page=last">{{ $thread->title }}</a>
                                            </td>
                                            <td class="col-time">
                                                <a href="{{ act('thread', 'view', $thread->id) }}?page=last#post-{{ $thread->last_post->id }}">{{ $thread->last_post->updated_at->diffForHumans() }}</a>
                                            </td>
                                            <td class="col-user">
                                                @avatar($thread->last_post->user inline)
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection