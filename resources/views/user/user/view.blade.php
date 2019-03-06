@title('User: '.$user->name)
@extends('app')

@section('content')
    <h1>
        <span class="fa fa-user"></span>
        {{ $user->name }}
        @if (permission('Admin') || (Auth::user() && Auth::user()->id == $user->id))
            <a href="{{ act('panel', 'index', $user->id) }}" class="btn btn-xs btn-outline-info">
                <span class="fa fa-cog"></span>
                {{ (Auth::user() && Auth::user()->id == $user->id) ? 'My' : $user->name."'s" }} Control Panel
            </a>
        @endif
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('user', 'index') }}">Users</a></li>
        <li class="active">View Profile</li>
    </ol>

    <div class="card">
        <div class="card-body">
            @include('user._profile', [ 'user' => $user ])
        </div>
    </div>

    <div class="row">

        <div class="col-md-6">
            <h2>
                <span class="fa fa-database"></span>
                Recent Vault Items
                <a class="btn btn-outline-primary btn-xs" href="{{ act('vault', 'index').'?users='.$user->id }}">See All</a>
            </h2>

            <div class="row vault-list pt-0">
                @forelse ($vault_items as $item)
                    <div class="col d-flex">
                        <a href="{{ act('vault', 'view', $item->id) }}" class="tile vault-item">
                            <span class="tile-heading">
                                <img class="game-icon" src="{{ $item->game->getIconUrl() }}" alt="{{ $item->game->name }}" title="{{ $item->game->name }}" />
                                <span title="{{ $item->name }}">{{ $item->name }}</span>
                            </span>
                            <span class="tile-main">
                                <img alt="{{ $item->name }}" src="{{ asset($item->getMediumAsset()) }}">
                            </span>
                            <span class="tile-title">By @avatar($item->user inline link=false)</span>
                            <span class="tile-subtitle">
                                @date($item->created_at) &bull;
                                <span class="stars">
                                    @if ($item->flag_ratings && $item->stat_ratings > 0)
                                        @foreach ($item->getRatingStars() as $star)
                                            <img src="{{ asset('images/stars/rating_'.$star.'.svg') }}" alt="{{ $star }} star" />
                                        @endforeach
                                        ({{$item->stat_ratings}})
                                    @elseif ($item->flag_ratings)
                                        No Ratings Yet
                                    @else
                                        Ratings Disabled
                                    @endif
                                </span>
                            </span>
                        </a>
                    </div>
                @empty
                    <div class="col text-center">
                        <em>None!</em>
                    </div>
                @endforelse
            </div>

        </div>

        <div class="col-md-6">
            <h2>
                <span class="fa fa-quote-left"></span>
                Recent Journals
                <a class="btn btn-outline-primary btn-xs" href="{{ act('journal', 'index').'?user='.$user->id }}">See All</a>
            </h2>

            <div class="journals">
                @forelse ($journals as $journal)
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
                                @date($journal->created_at)
                            </span>
                            <span class="bbcode d-block">
                                {!! app('bbcode')->ParseExcerpt($journal->content_text, 200, 'inline') !!}
                            </span>
                        </span>
                    </a>
                @empty
                    <div class="col text-center">
                        <em>None!</em>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
@endsection
