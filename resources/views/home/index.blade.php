@extends('app')

@section('content')
<div class="home-page">
    <div class="row">
        <div class="col-sm-8">
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
                <div class="col-xs-6">
                    <div class="vault-item tagged">
                        Two
                        <span class="tag right">Latest Map <span class="glyphicon glyphicon-certificate"></span></span>
                    </div>
                </div>

                <div class="col-xs-3 tagged">
                    <div class="vault-item">
                        One
                        <span class="tag small">Top Map</span>
                    </div>
                </div>
                <div class="col-xs-3 tagged">
                    <div class="vault-item">
                        Two
                        <span class="tag small">Top Map</span>
                    </div>
                </div>
                <div class="col-xs-3 tagged">
                    <div class="vault-item">
                        One
                        <span class="tag right small">New Map</span>
                    </div>
                </div>
                <div class="col-xs-3 tagged">
                    <div class="vault-item">
                        Two
                        <span class="tag right small">New Map</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
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
