@title('')
@extends('app')

@section('content')
<div class="home-page">

    <div class="row">
        <div class="col-8">
            <h1>
                <span class="fa fa-newspaper-o"></span>
                Latest News
                <a class="btn btn-outline-primary btn-xs" href="{{ act('news', 'index') }}">See all</a>
            </h1>
            <div class="news">
                @foreach ($newses as $news)
                    <div class="slot">
                        <div class="slot-heading">
                            <div class="slot-avatar">
                                @avatar($news->user small show_name=false)
                            </div>
                            <div class="slot-title">
                                <a href="{{ act('news', 'view', $news->id) }}">{{ $news->title }}</a>
                            </div>
                            <div class="slot-subtitle">
                                @avatar($news->user text) &bull;
                                @date($news->created_at) &bull;
                                <a href="{{ act('news', 'view', $news->id) }}">
                                    <span class="fa fa-comment"></span>
                                    {{ $news->stat_comments }} comment{{$news->stat_comments==1?'':'s'}}
                                </a>
                            </div>
                        </div>
                        <div class="slot-main">
                            <div class="bbcode">{!! $news->content_html !!}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="col-4">
            <h1>Welcome back!</h1>
            <ul>
                <li>This is your <strong>too many</strong><sup>th</sup> login</li>
                <li>You have no private messages</li>
                <li>Join the competition: <strong>3 brush challenge!</strong></li>
                <li>Say hello to <strong>Some douche</strong>, our newest member!</li>
            </ul>
        </div>
    </div>

    <h1>
        <span class="fa fa-database"></span>
        New in the Vault
        <a class="btn btn-outline-primary btn-xs" href="{{ act('vault', 'index') }}">See all</a>
    </h1>
    <div class="horizontal-scroll">
        @foreach ($new_maps as $item)
            <a href="{{ act('vault', 'view', $item->id) }}" class="tile">
                <span class="tile-main">
                    <img alt="{{ $item->name }}" src="{{ asset($item->getMediumAsset()) }}">
                </span>
                <span class="tile-title">{{ $item->name }}</span>
                <span class="tile-subtitle">@avatar($item->user inline link=false)</span>
            </a>
        @endforeach
    </div>


    <div class="row">

        <div class="col-md-8">
            <h1>
                <span class="fa fa-comments"></span>
                From the Forums
                <a class="btn btn-outline-primary btn-xs" href="{{ act('forum', 'index') }}">See all</a>
            </h1>
            <div class="forum">
                @foreach ($threads as $thread)
                    <div class="slot">
                        <div class="slot-heading">
                            <div class="slot-avatar">
                                @avatar($thread->last_post->user small show_name=false)
                            </div>
                            <div class="slot-title">
                                <a href="{{ act('thread', 'view', $thread->id) }}?page=last">{{ $thread->title }}</a>
                            </div>
                            <div class="slot-subtitle">
                                @avatar($thread->last_post->user text) &bull;
                                @date($thread->last_post->updated_at) &bull;
                                <a href="{{ act('thread', 'view', $thread->id) }}?page=last">
                                    <span class="fa fa-reply"></span>
                                    {{ $thread->stat_posts-1 }} repl{{$news->stat_comments==1?'y':'ies'}}
                                </a>
                            </div>
                        </div>
                        <div class="slot-main">
                            <div class="bbcode">{!! app('bbcode')->ParseExcerpt($thread->last_post->content_text) !!}</div>
                        </div>

                    </div>
                @endforeach
            </div>

        </div>

        <div class="col-md-4">

            <h1>
                <span class="fa fa-quote-left"></span>
                Member Journals
                <a class="btn btn-outline-primary btn-xs" href="{{ act('journal', 'index') }}">See all</a>
            </h1>
            <div class="journals">
                @foreach ($journals as $journal)
                    <a href="{{ act('journal', 'view', $journal->id) }}" class="slip">
                        <span class="slip-avatar">
                            @avatar($journal->user small link=false show_name=false)
                        </span>
                        <span class="slip-content">
                            <span class="slip-title">
                                {{ $journal->title }}
                            </span>
                            <span class="slip-subtitle">
                                @avatar($journal->user text link=false) &bull;
                                @date($journal->created_at)
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>


            <h1>
                <span class="fa fa-edit"></span>
                Wiki Edits
                <a class="btn btn-outline-primary btn-xs" href="{{ act('wiki', 'index') }}">View wiki</a>
            </h1>
            <div class="wiki-edits">
                @foreach ($wiki_edits as $obj)
                    <a href="{{ act('wiki', 'page', $obj->current_revision->slug) }}" class="slip">
                        <span class="slip-avatar">
                            @avatar($obj->current_revision->user small link=false show_name=false)
                        </span>
                        <span class="slip-content">
                            <span class="slip-title">
                                {{ $obj->current_revision->getNiceTitle($obj) }}
                            </span>
                            <span class="slip-subtitle">
                                @avatar($obj->current_revision->user text link=false) &bull;
                                @date($obj->current_revision->updated_at)
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    <div class="row">

        <div class="col-sm-5 col-md-4">

            <hc class="text-right hidden-xs">
                <h1>
                    <small><a href="{{ act('poll', 'index') }}">See all</a></small>
                    Poll
                </h1>
            </hc>
            <hc class="visible-xs-block">
                <h1>
                    Poll
                    <small><a href="{{ act('journal', 'index') }}">See all</a></small>
                </h1>
            </hc>
            <ul class="media-list">
                @foreach ($polls as $poll)
                    <li class="media media-panel" id="poll-{{ $poll->id }}">
                        <div class="media-body">
                            <div class="media-heading">
                                <h2>
                                    <a href="{{ act('poll', 'view', $poll->id) }}">{{ $poll->title }}</a>
                                    @if ($poll->isOpen())
                                        <small>Voting now!</small>
                                    @else
                                        <small>Voting closed</small>
                                    @endif
                                </h2>
                                @date($poll->created_at) &bull;
                                <a href="{{ act('poll', 'view', $poll->id) }}" class="btn btn-xs btn-link link">
                                    <span class="fa fa-comment"></span>
                                    {{ $poll->stat_comments }} comment{{$poll->stat_comments==1?'':'s'}}
                                </a>
                            </div>
                            <div class="bbcode">{!! $poll->content_html !!}</div>
                            <div class="well well-sm">
                                @if ($poll->isOpen() && Auth::user() && array_search($poll->id, $user_polls) === false)
                                    @include('polls/_form', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                                @else
                                    @include('polls/_results', [ 'poll' => $poll, 'user_votes' => $user_votes, 'front' => true ])
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
