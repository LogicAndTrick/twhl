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
            <h4>Recent Vault Items <small><a href="{{ act('vault', 'user', $user->id) }}">See all</a></small></h4>
            <ul>
                @foreach ($vault_items as $item)
                    <li>
                        <a href="{{ act('vault', 'view', $item->id) }}">{{ $item->name }}</a><br/>
                        <span>{{ $item->game->name }}</span><br/>
                        @if ($item->hasPrimaryScreenshot())
                            <img src="{{ asset('uploads/vault/'.$item->getPrimaryScreenshot()->image_small) }}" alt="{{ $item->name }}" />
                        @else
                            <img src="{{ asset('images/no-screenshot-160.png') }}" alt="{{ $item->name }}" />
                        @endif
                        <br/>
                        By: {{ $item->user->name }}<br/>
                        {{ Date::TimeAgo( $item->created_at ) }}
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-6">
            <h4>Recent Journals <small><a href="{{ act('journal', 'user', $user->id) }}">See all</a></small></h4>
            <ul>
                @foreach ($journals as $journal)
                    <li>{{ app('bbcode')->Parse(substr($journal->content_text, 0, 100)) }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
