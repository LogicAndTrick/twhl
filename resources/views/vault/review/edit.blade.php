@title('Edit vault item review for: '.$item->name)
@extends('app')

@section('content')
    <hc>
        <h1>Edit vault item review by @avatar($review->user inline)</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('vault', 'index') }}">Vault</a></li>
            <li><a href="{{ act('vault', 'view', $item->id) }}">{{ $item->name }}</a></li>
            <li class="active">Edit Review</li>
        </ol>
    </hc>

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

        @textarea(content_text $review) = Review Content
        <div class="form-group">
            <h4>
                Content preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div id="preview-panel" class="well bbcode">{!! $content ? app('bbcode')->Parse($content) : '' !!}</div>
        </div>
        <script type="text/javascript">
            $('#update-preview').click(function() {
                $('#preview-panel').html('Loading...');
                $.post('{{ url("api/format") }}?field=content_text', $('form').serializeArray(), function(data) {
                    $('#preview-panel').html(data);
                });
            });
        </script>

        @submit = Edit Review
    @endform

@endsection