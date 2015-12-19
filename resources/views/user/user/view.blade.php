@extends('app')

@section('content')
    <hc>
        @if (permission('Admin') || (Auth::user() && Auth::user()->id == $user->id))
            <a href="{{ act('panel', 'index', $user->id) }}" class="btn btn-xs btn-info">
                <span class="glyphicon glyphicon-cog"></span>
                {{ (Auth::user() && Auth::user()->id == $user->id) ? 'My' : $user->name."'s" }} Control Panel
            </a>
        @endif
        <h1>User: {{ $user->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('user', 'index') }}">Users</a></li>
            <li class="active">View Profile</li>
        </ol>
    </hc>
    <div class="panel panel-default">
        <div class="panel-body">
            @include('user._profile', [ 'user' => $user ])
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <hc>
                <h2>Recent Vault Items</h2>
                <ol class="breadcrumb">
                    <li><a href="{{ act('vault', 'index').'?users='.$user->id }}">See All</a></li>
                </ol>
            </hc>
            <ul class="row vault-list">
                @foreach ($vault_items as $item)
                    <li class="col-xs-12">
                        <div class="vault-item">
                            <img class="game-icon" src="{{ asset('images/games/' . $item->game->abbreviation . '_32.png') }}" alt="{{ $item->game->name }}" title="{{ $item->game->name }}" />
                            <a href="{{ act('vault', 'view', $item->id) }}">{{ $item->name }}</a>
                            <a class="screenshot" href="{{ act('vault', 'view', $item->id) }}">
                                <img src="{{ asset($item->getThumbnailAsset()) }}" alt="{{ $item->name }}" />
                            </a>
                            @if ($item->flag_ratings && $item->stat_ratings > 0)
                                <span class="stars">
                                    @foreach ($item->getRatingStars() as $star)
                                        <img src="{{ asset('images/stars/gold_'.$star.'_16.png') }}" alt="{{ $star }} star" />
                                    @endforeach
                                    ({{$item->stat_ratings}})
                                </span>
                            @elseif ($item->flag_ratings)
                                No Ratings Yet
                            @else
                                Ratings Disabled
                            @endif
                            <br/>
                            By @avatar($item->user inline)<br/>
                            @date($item->created_at)
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-6">
            <hc class="text-right">
                <h3>Recent Journals</h3>
                <ol class="breadcrumb">
                    <li><a href="{{ act('journal', 'user', $user->id) }}">See All</a></li>
                </ol>
            </hc>
            <ul>
                @foreach ($journals as $journal)
                    <li>{{ app('bbcode')->Parse(substr($journal->content_text, 0, 100)) }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
