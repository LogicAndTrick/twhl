@title('Edit vault item: '.$item->name)
@extends('app')

@section('content')
    <h1>Edit vault item: {{ $item->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('vault', 'index') }}">Vault</a></li>
        <li><a href="{{ act('vault', 'view', $item->id) }}">{{ $item->name }}</a></li>
        <li class="active">Edit Item</li>
    </ol>

    @form(vault/edit upload=true)
        @hidden(id $item)
        @autocomplete(engine_id api/engines $item) = Game Engine
        @autocomplete(game_id api/games $item) = Game
        @autocomplete(category_id api/vault-categories $item) = Category
        <p id="category-help" class="help-block"></p>
        @autocomplete(type_id api/vault-types $item) = Content Type
        @autocomplete(license_id api/licenses $item) = Content License
        <p id="license-help" class="help-block"></p>
        @text(name:item_name $item) = Name

        <div class="card mb-3">
            <div class="card-header">
                Files included in download
            </div>
            <div class="card-block">
                <div class="form-check form-check-inline">
                    @foreach ($includes as $inc)
                        <label class="form-check-label mr-1 {{ $type_id != $inc->type_id ? 'inactive' : '' }}" title="{{ $inc->description }}">
                            <input class="form-check-input" type="checkbox" name="__includes[]" value="{{ $inc->id }}" data-type="{{ $inc->type_id }}" {{
                                $type_id != $inc->type_id ? 'disabled' : ''
                            }} {{
                                is_array($__includes) && array_search($inc->id, $__includes) !== false ? 'checked' : ''
                            }} />
                            {{ $inc->name }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-3 option-panel">
            <div class="card-header">
                <span>File upload method:</span>
                <span class="form-check form-check-inline d-inline-block mb-0">
                    <label class="form-check-label ml-2">
                        <input class="form-check-input" type="radio" name="__upload_method" value="file" {{ $__upload_method != 'link' ? 'checked' : '' }} /> Upload file to TWHL
                    </label>
                    <label class="form-check-label ml-1">
                        <input class="form-check-input" type="radio" name="__upload_method" value="link" {{ $__upload_method == 'link' ? 'checked' : '' }} /> Link to file on another website
                    </label>
                </span>
            </div>
            <div class="card-block">
                @file(file) = File Upload (.zip, .rar, .7z, maximum size: 16mb) - Leave blank to use current file
                @text(link $location) = Link to File (Dropbox, Steam Workshop, etc.)
            </div>
        </div>

        @checkbox(flag_ratings $item) = Allow ratings for this content

        <div class="wikicode-input">
            @textarea(content_text $item) = Description
        </div>

        @submit = Edit Vault Item
    @endform
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function() {
            $('[name=game_id]').on('change', function() {
                var d = $(this).data('select2').data();
                if (d && d.length && d[0].engine_id) {
                    set_select2('[name=engine_id]', d[0].engine_id);
                }
            });
            $('[name=engine_id]').on('change', function() {
                var d = $(this).data('select2').data();
                var g = $('[name=game_id]').data('select2').data();
                if (d && d.length && d[0].id && g && g.length && g[0].engine_id != d[0].id) {
                    set_select2('[name=game_id]', null);
                }
            });
            $('[name=category_id]').on('change', function() {
                var d = $(this).data('select2').data();
                $('#category-help').text(d && d.length && d[0].description);
            });
            $('[name=license_id]').on('change', function() {
                var d = $(this).data('select2').data();
                $('#license-help').text(d && d.length && d[0].description);
            });
            $('[name=type_id]').on('change', function() {
                var d = $(this).data('select2').data();
                var id = d && d.length ? d[0].id : 0;
                $('[name="__includes[]"][data-type!="' + id + '"]')
                    .prop('disabled', true).prop('checked', false)
                    .parent().addClass('inactive');
                $('[name="__includes[]"][data-type="' + id + '"]').prop('disabled', false)
                    .parent().removeClass('inactive');
            });
            $('[name=__upload_method]').on('change', function() {
                var sel = $('[name=__upload_method]:checked').attr('value');
                $('.option-panel .card-block > div').addClass('d-none');
                $('[name="' + sel + '"]').parent().removeClass('d-none');
            }).change();
        });
    </script>
@endsection