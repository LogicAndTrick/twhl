@extends('app')

@section('content')
<div class="home-page">
    <div class="row">
        <div class="col-sm-9">
            <hc>
                <h1>New in the Vault</h1>
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
                <h1>New in the Wiki</h1>
            </hc>
            <hc class="visible-xs-block">
                <h1>New in the Wiki</h1>
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
        <div class="col-sm-8">
            <hc>
                <h1>From the Forums</h1>
            </hc>
        </div>
        <div class="col-sm-4">
            <hc class="text-right hidden-xs">
                <h1>Member Journals</h1>
            </hc>
            <hc class="visible-xs-block">
                <h1>Member Journals</h1>
            </hc>
        </div>
    </div>
</div>
@endsection
