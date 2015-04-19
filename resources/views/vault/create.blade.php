@extends('app')

@section('content')
    <h2>Upload to the Vault</h2>
    <p>Here you can upload a map, mod, texture pack, model, or other modding resources to the TWHL vault!</p>
    @form(vault/create upload=true)
        @autocomplete(engine_id api/engines) = Game Engine
        @autocomplete(game_id api/games) = Game
        @autocomplete(category_id api/vault-categories) = Category
        <p id="category-help" class="help-block"></p>
        @autocomplete(type_id api/vault-types) = Content Type
        @autocomplete(license_id api/licenses) = Content License
        <p id="license-help" class="help-block"></p>
        @text(name:item_name) = Name

        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Files included in download</strong>
            </div>
            <div class="panel-body">
                @foreach ($includes as $inc)
                    <label class="checkbox-inline {{ Request::old('type_id') != $inc->type_id ? 'inactive' : '' }}" title="{{ $inc->description }}">
                        <input type="checkbox" name="__includes[]" value="{{ $inc->id }}" data-type="{{ $inc->type_id }}" {{
                            Request::old('type_id') != $inc->type_id ? 'disabled' : ''
                        }} {{
                            is_array(Request::old('__includes')) && array_search($inc->id, Request::old('__includes')) !== false ? 'checked' : ''
                        }} />
                        {{ $inc->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="panel panel-default option-panel">
            <div class="panel-heading">
                <span>File upload method:</span>
                <label class="radio-inline">
                    <input type="radio" name="__upload_method" value="file" {{ Request::old('__upload_method') != 'link' ? 'checked' : '' }} /> Upload file to TWHL
                </label>
                <label class="radio-inline">
                    <input type="radio" name="__upload_method" value="link" {{ Request::old('__upload_method') == 'link' ? 'checked' : '' }} /> Link to file on another website
                </label>
            </div>
            <div class="panel-body">
                @file(file) = File Upload (.zip, .rar, .7z, maximum size: 16mb)
                @text(link) = Link to File (Dropbox, Steam Workshop, etc.)
            </div>
        </div>

        @file(screen) = (Optional) Upload a screenshot (.jpg, .png, maximum size: 2mb). You can add extra screenshots later.

        @checkbox(flag_notify) = Send me a private message when someone comments on this content
        @checkbox(flag_ratings) = Allow ratings for this content

        @textarea(content_text) = Description
        <div class="form-group">
            <h4>
                Description preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div id="preview-panel" class="well">{!! Request::old('content_text') ? app('bbcode')->Parse(Request::old('content_text')) : '' !!}</div>
        </div>

        @submit = Create Vault Item
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
                $('.option-panel .panel-body > div').addClass('hide');
                $('[name="' + sel + '"]').parent().removeClass('hide');
            }).change();
            $('#update-preview').click(function() {
                $('#preview-panel').html('Loading...');
                $.post('{{ url("api/format") }}?field=content_text', $('form').serializeArray(), function(data) {
                    $('#preview-panel').html(data);
                });
            });
        });
    </script>
@endsection