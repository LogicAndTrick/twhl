@extends('app')

@section('content')
<div class="home-page">
    <div class="row">
        <div class="col-sm-9">
            <hc>
                <h1>New in the Vault</h1>
            </hc>
            <div class="row vault-items">
                <div class="col-xs-6">
                    <div class="vault-item tagged">
                        One
                        <span class="tag"><span class="glyphicon glyphicon-star"></span> Map of the Month</span>
                    </div>
                </div>

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

                @foreach ($top_maps as $item)
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

                @foreach ($new_maps->take(-2) as $item)
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
            </div>
        </div>
        <div class="col-sm-3">
            <hc class="text-right hidden-xs">
                <h1>New in the Wiki</h1>
            </hc>
            <hc class="visible-xs-block">
                <h1>New in the Wiki</h1>
            </hc>
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
