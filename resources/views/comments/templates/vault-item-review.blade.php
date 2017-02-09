<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            Review by @avatar($obj->user inline) &bull;
            @date($comment->created_at)
            @if ($comment->hasRating())
                <span class="stars">
                    @foreach ($comment->getRatingStars() as $star)
                        <img src="{{ asset('images/stars/rating_'.$star.'.svg') }}" alt="{{ $star }} star" />
                    @endforeach
                </span>
            @endif
            @if($obj->isEditable())
                <a href="{{ act('vault-review', 'delete', $obj->id) }}" class="btn btn-danger btn-xs"><span class="fa fa-remove"></span> <span class="hidden-xs">Delete</span></a>
                <a href="{{ act('vault-review', 'edit', $obj->id) }}" class="btn btn-info btn-xs"><span class="fa fa-pencil"></span> <span class="hidden-xs">Edit</span></a>
            @endif
        </h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-4">
                <div class="vault-item-review-score rating-{{ $obj->getStarRating() }}">
                    {{ number_format(round($obj->getRating() * 10) / 10, 1) }}<small>/10</small>
                </div>
            </div>
            <div class="col-sm-8">
                Architecture &mdash; {{ round($obj->score_architecture * 10) / 10 }}<br/>
                Texturing &mdash; {{ round($obj->score_texturing * 10) / 10 }}<br/>
                Ambience &mdash; {{ round($obj->score_ambience * 10) / 10 }}<br/>
                Lighting &mdash; {{ round($obj->score_lighting * 10) / 10 }}<br/>
                Gameplay &mdash; {{ round($obj->score_gameplay * 10) / 10 }}
            </div>
        </div>
        <hr/>
        <div class="bbcode">{!! $obj->content_html !!}</div>
    </div>
</div>
