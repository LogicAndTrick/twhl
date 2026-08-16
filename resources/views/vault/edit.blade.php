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
        <p id="category-help" class="help-block form-text mx-4">{{ $item->vault_category->description }}</p>
        @autocomplete(type_id api/vault-types $item) = Content Type
        @autocomplete(license_id api/licenses $item) = Content License
        <p id="license-help" class="help-block form-text mx-4">{{ $item->license->description }}</p>
        @text(name:item_name $item required=true) = Name

        <div class="card mb-3">
            <div class="card-header">
                Files included in download
            </div>
            <div class="card-body">
                @foreach ($includes as $inc)
                    <label class="form-check-label me-1 {{ $type_id != $inc->type_id ? 'inactive' : '' }}" title="{{ $inc->description }}">
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

        <div class="card mb-3 option-panel">
            <div class="card-header">
                <span>File upload method:</span>
                <label class="form-check-label ms-2">
                    <input class="form-check-input" type="radio" name="__upload_method" value="file" {{ $__upload_method != 'link' ? 'checked' : '' }} /> Upload file to TWHL
                </label>
                <label class="form-check-label ms-1">
                    <input class="form-check-input" type="radio" name="__upload_method" value="link" {{ $__upload_method == 'link' ? 'checked' : '' }} /> Link to file on another website
                </label>
            </div>
            <div class="card-body">
                <fieldset name="file-fields">
                    @file(file required=!$has_upload) = File Upload (.zip, .rar, .7z, maximum size: 16mb)@if ($has_upload) - Leave blank to use current file @endif
                </fieldset>
                <fieldset name="link-fields">
                    @text(link $location format=url required=true) = Link to File (Dropbox, Steam Workshop, etc.)
                    @if (!$has_upload)
                        @checkbox(link_broken $item) = Broken download - Check this if the download link has stopped working
                    @endif
                </fieldset>
            </div>
        </div>

        @checkbox(flag_ratings $item) = Allow ratings for this content

        <div class="wikicode-input">
            @textarea(content_text $item required=true) = Description
        </div>

        @submit = Edit Vault Item
    @endform
@endsection

@section('scripts')
    @include('vault._vault_edit_js')
@endsection