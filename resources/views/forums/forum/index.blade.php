@title('Forum listings')
@extends('app')

@section('content')
    <hc>
        @if (permission('ForumAdmin'))
            @if ($show_deleted)
                <a class="btn btn-info btn-xs" href="{{ act('forum', 'index') }}"><span class="fa fa-eye-slash"></span> Hide deleted forums</a>
            @else
                <a class="btn btn-warning btn-xs" href="{{ act('forum', 'index') }}?deleted"><span class="fa fa-eye"></span> Show deleted forums</a>
            @endif
            <a class="btn btn-primary btn-xs" href="{{ act('forum', 'create') }}"><span class="fa fa-plus"></span> Create new forum</a>
        @endif
        <h1><span class="fa fa-comments"></span> Forum listing</h1>
    </hc>
    <ul class="media-list forum-listing">
        @foreach ($forums as $forum)
            <li class="media media-panel {{ $forum->deleted_at ? 'inactive' : '' }}">
                <div class="media-body">
                    <div class="media-heading">
                        @if (permission('ForumAdmin'))
                            @if ($forum->deleted_at)
                                <a href="{{ act('forum', 'restore', $forum->id) }}" class="btn btn-xs btn-info"><span class="fa fa-repeat"></span></a>
                            @else
                                <a href="{{ act('forum', 'delete', $forum->id) }}" class="btn btn-xs btn-danger"><span class="fa fa-remove"></span></a>
                                <a href="{{ act('forum', 'edit', $forum->id) }}" class="btn btn-xs btn-primary"><span class="fa fa-pencil"></span></a>
                            @endif
                        @endif
                        <h2>
                            <a href="{{ act('forum', 'view', $forum->slug) }}">
                                <span class="forum-icon {{ $forum->getIconClasses() }}"></span>
                                {{ $forum->name }}
                            </a>
                            <small class="pull-right">{{ $forum->stat_posts }} posts in {{ $forum->stat_threads }} threads</small>
                        </h2>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="description">
                                {{ $forum->description }}
                            </p>
                        </div>
                        <div class="col-md-8 forum-info">
                            <table class="table table-condensed recent-forum-threads">
                                <thead>
                                    <tr>
                                        <th class="col-thread">Recently Active Threads <a class="see-all" href="{{ act('forum', 'view', $forum->slug) }}">See all</a></th>
                                        <th class="col-time">Last Post</th>
                                        <th class="col-user">By User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recent_threads->where('forum_id', $forum->id) as $thread)
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
            </li>
        @endforeach
    </ul>
@endsection