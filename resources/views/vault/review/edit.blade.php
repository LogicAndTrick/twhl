@title('Edit vault item review for: '.$item->name)
@extends('app')

@section('content')
    <h1>Edit vault item review by @avatar($review->user inline)</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('vault', 'index') }}">Vault</a></li>
        <li><a href="{{ act('vault', 'view', $item->id) }}">{{ $item->name }}</a></li>
        <li class="active">Edit Review</li>
    </ol>

    @form(vault-review/edit)
        @hidden(id $review)
        <div class="row">
            <div class="col-md-2 col-sm-4 col-xs-6">
                <h3 class="review-scores-label">Scores:</h3>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                @text(score_architecture $review) = Architecture (0-10)
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                @text(score_texturing $review) = Texturing (0-10)
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                @text(score_ambience $review) = Ambience (0-10)
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                @text(score_lighting $review) = Lighting (0-10)
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                @text(score_gameplay $review) = Gameplay (0-10)
            </div>
        </div>

        <div class="wikicode-input">
            @textarea(content_text $review) = Review Content
        </div>

        @submit = Edit Review
    @endform

@endsection