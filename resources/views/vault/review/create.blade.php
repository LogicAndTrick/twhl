@title('Add vault item review: '.$item->name)
@extends('app')

@section('content')
    <hc>
        <h1>Add vault item review</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('vault', 'index') }}">Vault</a></li>
            <li><a href="{{ act('vault', 'view', $item->id) }}">{{ $item->name }}</a></li>
            <li class="active">Add Review</li>
        </ol>
    </hc>
    <div class="alert alert-info">
        <h2>
            Reviewing: {{ $item->name }}
            by @avatar($item->user inline)
        </h2>
        <p>
            Reviews allow you to rate a map in more depth than a regular comment.
            If you have not yet added a star rating to the map, posting a review will
            automatically assign a star rating based on your review score.
            (0% - 20% = 1 star, 20 - 40% = 2 stars, etc)
        </p>
    </div>

    @form(vault-review/create)
        @hidden(item_id $item->id)
        <div class="row">
            <div class="col-md-2 col-sm-4 col-xs-6">
                <h3 class="review-scores-label">Scores:</h3>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                @text(score_architecture) = Architecture (0-10)
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                @text(score_texturing) = Texturing (0-10)
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                @text(score_ambience) = Ambience (0-10)
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                @text(score_lighting) = Lighting (0-10)
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                @text(score_gameplay) = Gameplay (0-10)
            </div>
        </div>

        @textarea(content_text) = Review Content
        <div class="form-group">
            <h4>
                Content preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div id="preview-panel" class="card card-block bbcode">{!! Request::old('content_text') ? app('bbcode')->Parse(Request::old('content_text')) : '' !!}</div>
        </div>
        <script type="text/javascript">
            $('#update-preview').click(function() {
                $('#preview-panel').html('Loading...');
                $.post('{{ url("api/posts/format") }}?field=content_text', $('form').serializeArray(), function(data) {
                    $('#preview-panel').html(data);
                });
            });
        </script>

        @submit = Add Review
    @endform

@endsection