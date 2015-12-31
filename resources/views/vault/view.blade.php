@extends('app')

@section('content')
    <hc>
        @if (permission('VaultAdmin'))
            <a class="btn btn-xs btn-danger" href="{{ act('vault', 'delete', $item->id) }}"><span class="glyphicon glyphicon-remove"></span> Delete</a>
        @endif
        @if ($item->isEditable())
            <a class="btn btn-xs btn-info" href="{{ act('vault', 'edit-screenshots', $item->id) }}"><span class="glyphicon glyphicon-picture"></span> Edit Screenshots</a>
            <a class="btn btn-xs btn-primary" href="{{ act('vault', 'edit', $item->id) }}"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
        @endif
        <h1>
            <img src="{{ asset('images/games/' . $item->game->abbreviation . '_32.png') }}" alt="{{ $item->game->name }}" title="{{ $item->game->name }}" />
            {{ $item->name }} by @avatar($item->user inline)
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('vault', 'index') }}">Vault</a></li>
            <li class="active">View Vault Item</li>
        </ol>
    </hc>

    <div id="vault-slider" class="slider">
        <div class="loading" data-u="loading">
            Loading...
        </div>
        <div class="slides" data-u="slides">
            @if (count($item->vault_screenshots) > 0)
                @foreach($item->vault_screenshots->sortBy('order_index') as $sshot)
                <div>
                    <img data-u="image" data-src2="{{ asset('uploads/vault/'.$sshot->image_large) }}" alt="Screenshot" />
                    <img data-u="thumb" data-src2="{{ asset('uploads/vault/'.$sshot->image_thumb) }}" alt="Thumbnail" />
                </div>
                @endforeach
            @else
                <div>
                    <img data-u="image" data-src2="{{ asset('images/no-screenshot-640.png') }}" alt="Screenshot" />
                    <img data-u="thumb" data-src2="{{ asset('images/no-screenshot-320.png') }}" alt="Thumbnail" />
                </div>
            @endif
        </div>
        <div data-u="thumbnavigator" class="thumbs">
            <div data-u="slides">
                <div data-u="prototype" class="p">
                    <div data-u="thumbnailtemplate" class="i"></div>
                </div>
            </div>
        </div>
        <span data-u="arrowleft" class="arrow left" style="top: 123px; left: 8px;"></span>
        <span data-u="arrowright" class="arrow right" style="top: 123px; right: 8px;"></span>
    </div>

    @foreach ($item->motms as $motm)
        <div class="alert alert-success">
            <h3>
                <span class="glyphicon glyphicon-certificate"></span>
                Map of the Month winner for {{ $motm->getDateString() }}!
            </h3>
        </div>
    @endforeach

    <div class="row">
        <div class="col-sm-8">
            <hc>
                <h2>Item Description</h2>
            </hc>
            <div class="bbcode">{!! $item->content_html !!}</div>
        </div>
        <div class="col-sm-4 vault-key-info">
            <hc class="text-right hidden-xs">
                <h2>Key Information</h2>
            </hc>
            <hc class="visible-xs-block">
                <h2>Key Information</h2>
            </hc>
            @if ($item->flag_ratings && $item->stat_ratings > 0)
                <span class="stars">
                    @foreach ($item->getRatingStars() as $star)
                        <img src="{{ asset('images/stars/gold_'.$star.'_32.png') }}" alt="{{ $star }} star" />
                    @endforeach
                </span>
            @endif
            <dl class="dl-horizontal dl-tiny dl-unresponsive">
                <dt>Name</dt><dd>{{ $item->name }}</dd>
                <dt>By</dt><dd>@avatar($item->user inline)</dd>
                <dt>Type</dt><dd>{{ $item->vault_type->name }}</dd>
                <dt>Engine</dt><dd>{{ $item->engine->name }}</dd>
                <dt>Game</dt><dd>{{ $item->game->name }}</dd>
                <dt>Category</dt><dd>{{ $item->vault_category->name }}</dd>
                @if (count($item->vault_includes) > 0)
                    <dt>Included</dt><dd>{{ implode(', ', array_map(function($x) { return $x['name']; }, $item->vault_includes->toArray())) }}</dd>
                @endif
                <dt>Created</dt><dd>@date($item->created_at)</dd>
                <dt>Updated</dt><dd>@date($item->updated_at)</dd>
                <dt>Views</dt><dd>{{ $item->stat_views }}</dd>
                <dt>Downloads</dt><dd>{{ $item->stat_downloads }}</dd>
                <dt>Comments</dt><dd>{{ $item->stat_comments }}</dd>
                @if ($item->flag_ratings && $item->stat_ratings > 0)
                    <dt>Rating</dt><dd>{{ $item->stat_average_rating }} ({{ $item->stat_ratings }})</dd>
                @endif
                @if ($item->reviewsAllowed())
                    <dt>Reviews</dt><dd>{{ $item->vault_item_reviews->count() }}</dd>
                @endif
            </dl>
            @if ($item->license_id == 1)
                <button type="button" class="btn btn-default btn-block license-button" data-toggle="tooltip" data-placement="top" title="{{ $item->license->description }}">
                    <span class="glyphicon glyphicon-copyright-mark"></span>
                    License: {{ $item->license->name }}
                </button>
            @else
                <a href="{{ preg_replace('%.*(http://[^ ]*).*%i', '$1', $item->license->description) }}" target="_blank" class="btn btn-default btn-block license-button" data-toggle="tooltip" data-placement="top" title="{{
                    preg_replace('%\s*(http://[^ ]*)\s*%i', '', $item->license->description)
                }}">
                    <span class="glyphicon glyphicon-copyright-mark"></span>
                    License: {{ $item->license->name }}
                </a>
            @endif
            <a href="{{ act('vault', 'download', $item->id) }}" target="_blank" class="btn btn-success btn-block" style="margin-top: 5px;">
                <span class="glyphicon glyphicon-download-alt"></span>
                Download
                @if ($item->file_size > 0)
                    ({{ format_filesize($item->file_size) }})
                @elseif ($item->is_hosted_externally)
                    (Hosted Externally)
                @endif
            </a>
            @if ($item->reviewsAllowed())
                <div class="text-center review-info">
                    @if ($item->hasReviews())
                        Reviews: {{ $item->vault_item_reviews->count() }}
                    @else
                        No Reviews yet
                    @endif
                    <br/>
                    @if ($user_review)
                        <a class="btn btn-primary" href="#comment-{{ $user_review->comment_id }}">
                            <span class="glyphicon glyphicon-star"></span>
                            Your review score: <strong class="rating-{{ $user_review->getStarRating() }}">{{ number_format(round($user_review->getRating() * 10) / 10, 1) }}</strong>
                        </a>
                    @else
                        <a class="btn btn-primary" href="{{ act('vault-review', 'create', $item->id) }}">
                            <span class="glyphicon glyphicon-star"></span>
                            Post a review
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @include('comments.list', [ 'article' => $item, 'comments' => $comments, 'article_type' => \App\Models\Comments\Comment::VAULT, 'article_id' => $item->id, 'inject_add' => ['vault.review-info' => ['item' => $item, 'user_review' => $user_review]] ])
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()

            var slider = new $JssorSlider$("vault-slider", {
                $AutoPlay: true,
                $AutoPlayInterval: 4000,
                $SlideDuration: 250,
                $FillMode: 5,

                $ThumbnailNavigatorOptions: {
                    $Class: $JssorThumbnailNavigator$,
                    $ChanceToShow: 2,
                    $SpacingX: 8,
                    $DisplayPieces: 10,
                    $ParkingPosition: 360
                },

                $ArrowNavigatorOptions: {
                    $Class: $JssorArrowNavigator$,
                    $AutoCenter: 2
                }
            });
        });
    </script>
@endsection