@title('')
@extends('app')

@section('content')
<div class="home-page">

    <div class="row">
        <div class="col-md-8 order-last order-md-first">
            <h1>
                <a href="{{ act('news', 'index') }}"><span class="fa fa-newspaper-o"></span> Latest News</a>
                <a class="btn btn-outline-primary btn-xs hidden-sm-down" href="{{ act('news', 'index') }}">See all</a>
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
        <div class="col-md-4">
            <div class="welcome">
                @if (Auth::check())
                    <h1>
                        <span class="fa fa-user"></span>
                        Welcome back!
                        <a class="btn btn-outline-primary btn-xs hidden-md-down" href="{{ act('user', 'view', Auth::user()->id) }}">My profile</a>
                    </h1>
                    <div class="slot d-none"></div>
                @endif
                <div class="slot">
                    @if (Auth::check())
                        {? $unread_count = Auth::user()->unreadPrivateMessageCount(); ?}
                        {? $notify_count = Auth::user()->unreadNotificationCount(); ?}
                        {? $user = Auth::user(); ?}
                        <div class="user slot-heading">
                            <div>
                                @avatar($user small show_name=false show_border=true)
                            </div>
                            <div>
                                <h3>{{$user->name}}</h3>
                                <a href="{{ act('message', 'index') }}" class="{{ $unread_count > 0 ? 'notify-alert' : '' }}">
                                    <span>
                                        <span class="fa fa-envelope"></span>
                                        {{ $unread_count == 0 ? 'Private messages' : $unread_count . ' new private message' . ($unread_count == 1 ? '' : 's') }}
                                    </span>
                                </a>
                                <span class="hidden-md-only hidden-lg-only">&bull;</span>
                                <br class="hidden-sm-down hidden-xl-up" />
                                <a href="{{ act('panel', 'notifications') }}" class="d-inline-block pb-1 {{ $notify_count > 0 ? 'notify-alert' : '' }}">
                                    <span>
                                        <span class="fa fa-bell"></span>
                                        {{ $notify_count == 0 ? 'Notifications' : $notify_count . ' new notification' . ($notify_count == 1 ? '' : 's') }}
                                    </span>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="special-welcome">
                            <h1>
                                Welcome to TWHL!
                            </h1>
                            <p>
                                Since the dawn of time, humanity has sought the answer to one simple question:
                                <em>How do I create content for Half-Life?</em>
                            </p>
                            <p>
                                TWHL is a community which answers that question (and many others) with tutorials, resources, and forums!
                                Click the <strong>Login/Register</strong> button in the navigation above to get started.
                            </p>
                        </div>
                    @endif

                    <h3 class="pl-4 mb-0">What's New</h3>
                    {? $data = header_data() ?}
                    <ul class="pl-4">
                        @if (Auth::check())
                            <li style="list-style-type: none; margin-left: -1rem;">
                                <span class="fa fa-sign-in"></span>
                                This is your <strong>{{ Auth::user()->stat_logins }}</strong><sup>{{ ordinal(Auth::user()->stat_logins, false) }}</sup> login
                            </li>
                        @endif
                        @if ($data['competition'])
                            {? $comp = $data['competition'] ?}
                            @if ($comp->isVotingOpen())
                                <li>Vote for a winner in the <a href="{{ act('competition', 'vote', $comp->id) }}">{{ $comp->name }}</a> competition!</li>
                            @elseif ($comp->isOpen())
                                <li>Enter our newest competition, <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a>!</li>
                            @elseif ($comp->isJudging() || $comp->isVoting())
                                <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a> competition results coming soon...</li>
                            @elseif ($comp->isClosed())
                                <li>Check out <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a> competition results!</li>
                            @else
                                <li>Take a look at our latest competition, <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a>!</li>
                            @endif
                        @endif
                        @if ($data['user'])
                            {? $user = $data['user'] ?}
                            <li>Say hello to <a href="{{ act('user', 'view', $user->id) }}">{{ $user->name }}</a>, our newest member!</li>
                        @endif
                    </ul>
                </div>
                <div class="slot px-3" style="border-width: 4px;">
                    <h3>Let's get started!</h3>
                    <p class="mb-1">Ready to start modding? Our <a href="{{ act('wiki', 'index') }}">wiki</a> has all the information you need! Start here:</p>
                    <ul class="mb-2">
                        <li><a href="{{ act('wiki', 'page', \App\Models\Wiki\WikiRevision::CreateSlug('Tools and Resources')) }}"><span class="fa fa-rocket"></span> Tools and Resources</a></li>
                        <li><a href="{{ act('wiki', 'page', \App\Models\Wiki\WikiRevision::CreateSlug('category:Tutorials')) }}"><span class="fa fa-book"></span> Tutorials</a></li>
                        <li><a href="{{ act('wiki', 'page', \App\Models\Wiki\WikiRevision::CreateSlug('category:Entity Guides')) }}"><span class="fa fa-info-circle"></span> Entity Guides</a></li>
                    </ul>
<?php
                    $wiki_spotlight = count($wiki_articles['featured_tutorials']) > 0 ? $wiki_articles['featured_tutorials'][0] : null;
?>
                    @if ($wiki_spotlight)
                        <div class="text-center">
                            <span class="fa fa-star"></span> Today's featured tutorial:<br/>
                            <a href="{{ act('wiki', 'page', $wiki_spotlight->slug) }}">{{ $wiki_spotlight->title }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if (count($competitions) > 0)
        <h1>
            <a href="{{ act('competition', 'index') }}"><span class="fa fa-trophy"></span> Active Competitions</a>
            <a class="btn btn-outline-primary btn-xs hidden-sm-down" href="{{ act('competition', 'index') }}">See all</a>
        </h1>
        <div class="competition-list">
            <div class="slot">
                <div class="slot-main">
                    @foreach ($competitions->sortByDesc('close_date') as $comp)
                        @if (!$loop->first)
                            <hr />
                        @endif
                        <div class="row">
                            <div class="col-6 col-sm-4 d-flex flex-column align-items-start">
                                <h2 class="font-weight-normal my-0">
                                    <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a>
                                </h2>
                                <small>{{ $comp->type->name }} &bull; {{ $comp->judge_type->name }}</small>
                            </div>
                            <div class="col-6 col-sm-4 d-flex align-items-center justify-content-center">
                                <h2 class="font-weight-normal my-0 text-center">{{ $comp->getStatusText() }}</h2>
                            </div>
                            <div class="col-12 justify-content-center col-sm-4 d-flex align-items-center justify-content-sm-end pt-3 pt-sm-0">
                                <div class="d-inline-block">
                                    @if ($comp->isOpen())
                                        <a href="{{ act('competition', 'brief', $comp->id) }}" class="btn btn-success">Enter Now</a>
                                    @elseif ($comp->isVotingOpen())
                                        <a href="{{ act('competition', 'vote', $comp->id) }}" class="btn btn-info">{{ $comp->canVote() ? 'Vote Now' : 'View Entries' }}</a>
                                    @elseif ($comp->isVoting() || $comp->isJudging())
                                        Results coming soon!
                                    @else
                                        {{ $comp->isActive() ? 'Closes' : 'Closed' }} @date($comp->close_date) ({{ $comp->close_date->format('jS F Y') }})
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <h1>
        <a href="{{ act('vault', 'index') }}"><span class="fa fa-database"></span> New in the Vault</a>
        <a class="btn btn-outline-primary btn-xs hidden-sm-down" href="{{ act('vault', 'index') }}">See all</a>
    </h1>
    <div class="vault-items">
        <div class="horizontal-scroll">
            @foreach ($new_maps as $item)
                <a href="{{ act('vault', 'view', $item->id) }}" class="tile tagged">
                    @if ($item->created_at < $item->updated_at->subWeeks(2))
                        <span class="tag"><span class="fa fa-certificate"></span> Updated</span>
                    @endif
                    <span class="tile-main">
                        <img alt="{{ $item->name }}" src="{{ asset($item->getMediumAsset()) }}">
                    </span>
                    <span class="tile-title">{{ $item->name }}</span>
                    <span class="tile-subtitle">@avatar($item->user inline link=false)</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="row">

        <div class="col-md-8">
            <h1>
                <a href="{{ act('forum', 'index') }}"><span class="fa fa-comment"></span> From the Forums</a>
                <a class="btn btn-outline-primary btn-xs hidden-sm-down" href="{{ act('forum', 'index') }}">See all</a>
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
                                    {{ $thread->stat_posts-1 }} repl{{$thread->stat_posts-1==1?'y':'ies'}}
                                </a>
                            </div>
                        </div>
                        <div class="slot-main">
                            <div class="bbcode">{!! bbcode_excerpt($thread->last_post->content_text) !!}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <h1>
                <a href="{{ act('poll', 'index') }}"><span class="fa fa-pie-chart"></span> Poll</a>
                <a class="btn btn-outline-primary btn-xs hidden-sm-down" href="{{ act('poll', 'index') }}">See all</a>
            </h1>
            <div class="poll">
                @foreach ($polls as $poll)
                    <div class="slot" id="poll-{{ $poll->id }}">
                        <div class="slot-heading">
                            <div class="slot-title">
                                <a href="{{ act('poll', 'view', $poll->id) }}">{{ $poll->title }}</a>
                            </div>
                            <div class="slot-subtitle">
                                Posted @date($poll->created_at) &bull;
                                {{ $poll->isOpen() ? 'Voting now!' : 'Voting closed' }} &bull;
                                <a href="{{ act('poll', 'view', $poll->id) }}">
                                    <span class="fa fa-comment"></span>
                                    {{ $poll->stat_comments }} comment{{$poll->stat_comments==1?'':'s'}}
                                </a>
                            </div>
                        </div>
                        <div class="slot-main">
                            <div class="bbcode">{!! $poll->content_html !!}</div>
                            <div class="card card-body">
                                @if ($poll->isOpen() && Auth::user() && array_search($poll->id, $user_polls) === false)
                                    @include('polls/_form', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                                @else
                                    @include('polls/_results', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        <div class="col-md-4">

            <h1>
                <a href="{{ act('journal', 'index') }}"><span class="fa fa-quote-left"></span> Journals</a>
                <a class="btn btn-outline-primary btn-xs hidden-sm-down" href="{{ act('journal', 'index') }}">See all</a>
            </h1>
            <div class="journals">
                @foreach ($journals as $journal)
                    <a href="{{ act('journal', 'view', $journal->id) }}" class="slip">
                        <span class="slip-avatar">
                            @avatar($journal->user small link=false show_name=false)
                        </span>
                        <span class="slip-content">
                            <span class="slip-title">
                                {{ $journal->getTitle() }}
                            </span>
                            <span class="slip-subtitle">
                                @avatar($journal->user text link=false) &bull;
                                <span class="hidden-md-only">@date($journal->created_at) &bull;</span>
                                <span class="fa fa-comment"></span> {{ $journal->stat_comments }}
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>

            <h1>
                <a href="{{ act('wiki', 'index') }}"><span class="fa fa-lightbulb-o"></span> Resources</a>
                <a class="btn btn-outline-primary btn-xs hidden-sm-down" href="{{ act('wiki', 'index') }}">View wiki</a>
            </h1>
            <div class="slot wiki-feature">
                <h2>Featured tutorials</h2>
                <ul>
                    @foreach ($wiki_articles['featured_tutorials'] as $wa)
                        <li>
                            <a href="{{ act('wiki', 'page', $wa->slug) }}">{{ $wa->title }}</a>
                        </li>
                    @endforeach
                </ul>
                <div class="text-center my-2">
                    <a href="{{ act('wiki', 'page', \App\Models\Wiki\WikiRevision::CreateSlug('category:Tutorials')) }}" class="btn btn-primary btn-xs">
                        <span class="fa fa-chevron-circle-right"></span>
                        See all tutorials
                    </a>
                </div>
                <h2>Recently edited pages</h2>
                <ul>
                    @foreach ($wiki_articles['recent_edits'] as $obj)
                        <li>
                            <a href="{{ act('wiki', 'page', $obj->current_revision->slug) }}" class="d-block">
                                {{ $obj->current_revision->getNiceTitle($obj) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <h1>
                <span class="fa fa-globe"></span> Active Users
            </h1>
            <div class="slot active-users">
                <ul class="list-unstyled">
                    @foreach ($onliners as $o)
                        <li class="d-flex flex-row">
                            @avatar($o inline)
                            <em class="ms-auto">
                                {{ $o->last_access_time->diffForHumans() }}
                            </em>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
