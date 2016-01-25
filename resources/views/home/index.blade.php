@title('')
@extends('app')

@section('content')
<div class="home-page">
    <div class="row">
        <div class="col-sm-9">
            <hc>
                <h1>
                    New in the Vault
                    <small><a href="{{ act('vault', 'index') }}">See all</a></small>
                </h1>
            </hc>
            <div class="row vault-items">

                @if ($motm)
                    <div class="col-xs-6 tagged">
                        <a href="{{ act('vault', 'view', $motm->vault_item->id) }}" class="vault-item" style="background-image: url('{{ asset($motm->vault_item->getMediumAsset()) }}');">
                            <span class="tag"><span class="glyphicon glyphicon-star"></span> Map of the Month</span>
                            <span class="vault-item-details">
                                @avatar($motm->vault_item->user small link=false)<hr />
                                <span class="large-title">{{ $motm->vault_item->name }}</span>
                            </span>
                        </a>
                    </div>
                @endif

                @foreach ($new_maps->take(1) as $item)
                    <div class="col-xs-6 tagged">
                        <a href="{{ act('vault', 'view', $item->id) }}" class="vault-item" style="background-image: url('{{ asset($item->getMediumAsset()) }}');">
                            <span class="tag right">Latest Map <span class="glyphicon glyphicon-certificate"></span></span>
                            <span class="vault-item-details">
                                @avatar($item->user small link=false)<hr />
                                <span class="large-title">{{ $item->name }}</span>
                            </span>
                        </a>
                    </div>
                @endforeach

                @foreach ($top_maps->slice(0, 2) as $item)
                <div class="col-xs-3 tagged">
                    <a href="{{ act('vault', 'view', $item->id) }}" class="vault-item" style="background-image: url('{{ asset($item->getThumbnailAsset()) }}');">
                        <span class="tag small">Top Map</span>
                        <span class="vault-item-details">
                            @avatar($item->user inline link=false)<hr />
                            {{ $item->name }}
                        </span>
                    </a>
                </div>
                @endforeach

                @foreach ($new_maps->slice(1, 2) as $item)
                <div class="col-xs-3 tagged">
                    <a href="{{ act('vault', 'view', $item->id) }}" class="vault-item" style="background-image: url('{{ asset($item->getThumbnailAsset()) }}');">
                        <span class="tag right small">New Map</span>
                        <span class="vault-item-details">
                            @avatar($item->user inline link=false)<hr />
                            {{ $item->name }}
                        </span>
                    </a>
                </div>
                @endforeach

                @foreach ($top_maps->slice(2, 3) as $item)
                <div class="col-xs-2 tagged">
                    <a href="{{ act('vault', 'view', $item->id) }}" class="vault-item" style="background-image: url('{{ asset($item->getThumbnailAsset()) }}');">
                        <span class="tag small">Top</span>
                        <span class="vault-item-details small">
                            @avatar($item->user inline link=false)<hr />
                            {{ $item->name }}
                        </span>
                    </a>
                </div>
                @endforeach

                @foreach ($new_maps->slice(3, 3) as $item)
                <div class="col-xs-2 tagged">
                    <a href="{{ act('vault', 'view', $item->id) }}" class="vault-item" style="background-image: url('{{ asset($item->getThumbnailAsset()) }}');">
                        <span class="tag right small">New</span>
                        <span class="vault-item-details small">
                            @avatar($item->user inline link=false)<hr />
                            {{ $item->name }}
                        </span>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        <div class="col-sm-3">
            <hc class="text-right hidden-xs">
                <h1>
                    <small><a href="{{ act('wiki', 'index') }}">View <span class="hidden-sm">wiki</span></a></small>
                    Wiki Edits
                </h1>
            </hc>
            <hc class="visible-xs-block">
                <h1>
                    Wiki Edits
                    <small><a href="{{ act('wiki', 'index') }}">View wiki</a></small>
                </h1>
            </hc>
            <ul class="wiki-edits">
                @foreach ($wiki_edits as $obj)
                    <li>
                        <a class="title" href="{{ act('wiki', 'page', $obj->current_revision->slug) }}">
                            {{ $obj->current_revision->getNiceTitle($obj) }}
                        </a>
                        <span class="info">
                            @date($obj->current_revision->created_at) by @avatar($obj->current_revision->user inline)
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-7 col-md-8">
            <hc>
                <h1>
                    From the Forums
                    <small><a href="{{ act('forum', 'index') }}">See all</a></small>
                </h1>
            </hc>
            <ul class="media-list recent-threads">
                @foreach ($threads as $thread)
                    <li class="media media-panel">
                        <div class="row">
                            <div class="col-xs-8">
                                <div class="media-body">
                                    <div class="media-heading">
                                        <h3><a href="{{ act('thread', 'view', $thread->id) }}?page=last">{{ $thread->title }}</a></h3> &bull;
                                        @date($thread->last_post->updated_at) &bull;
                                        @avatar($thread->last_post->user inline)
                                    </div>
                                    <div class="bbcode">{!! app('bbcode')->ParseExcerpt($thread->last_post->content_text) !!}</div>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <strong>Other users in thread</strong>
                                <ul class="other-users">
                                    {? $users_in_thread = $thread_users->where('thread_id', $thread->id); ?}
                                    @if ($users_in_thread->count() > 0)
                                        @foreach ($users_in_thread as $user)
                                            <li>
                                                @avatar($user inline)
                                            </li>
                                        @endforeach
                                    @else
                                        <li>Nobody else!</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <hc>
                <h1>
                    News
                    <small><a href="{{ act('news', 'index') }}">See all</a></small>
                </h1>
            </hc>
            <ul class="media-list">
                @foreach ($newses as $news)
                    <li class="media media-panel" id="news-{{ $news->id }}">
                        <div class="media-left">
                            <div class="media-object">
                                @avatar($news->user small show_border=true show_name=false)
                            </div>
                        </div>
                        <div class="media-body">
                            <div class="media-heading">
                                <h2><a href="{{ act('news', 'view', $news->id) }}">{{ $news->title }}</a></h2>
                                @avatar($news->user text) &bull;
                                @date($news->created_at) &bull;
                                <a href="{{ act('news', 'view', $news->id) }}" class="btn btn-xs btn-link link">
                                    <span class="glyphicon glyphicon-comment"></span>
                                    {{ $news->stat_comments }} comment{{$news->stat_comments==1?'':'s'}}
                                </a>
                            </div>
                          <div class="bbcode">{!! $news->content_html !!}</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-sm-5 col-md-4">
            <hc class="text-right hidden-xs">
                <h1>
                    <small><a href="{{ act('journal', 'index') }}">See all</a></small>
                    Member Journals
                </h1>
            </hc>
            <hc class="visible-xs-block">
                <h1>
                    Member Journals
                    <small><a href="{{ act('journal', 'index') }}">See all</a></small>
                </h1>
            </hc>
            <ul class="media-list">
                @foreach ($journals as $journal)
                    <li class="media media-panel">
                        <div class="media-body">
                            <div class="media-heading">
                                @avatar($journal->user inline) &bull;
                                @date($journal->created_at) &bull;
                                <a href="{{ act('journal', 'view', $journal->id) }}" class="btn btn-xs btn-link link">
                                    <span class="glyphicon glyphicon-comment"></span>
                                    {{ $journal->stat_comments }} comment{{$journal->stat_comments==1?'':'s'}}
                                </a>
                            </div>
                            <div class="bbcode">{!! app('bbcode')->ParseExcerpt($journal->content_text) !!}</div>
                            <div class="text-right">
                                <a href="{{ act('journal', 'view', $journal->id) }}" class="btn btn-xs btn-link link">
                                    Read full journal <span class="glyphicon glyphicon-chevron-right"></span>
                                </a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
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
                                    <span class="glyphicon glyphicon-comment"></span>
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
