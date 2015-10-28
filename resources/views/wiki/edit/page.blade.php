@extends('app')

@section('content')
    @include('wiki.nav', ['revision' => $revision])
    <h2>{{ $revision->title }}</h2>
    @form(wiki/edit upload=true)
        @hidden(id $revision)
        @if ($revision->wiki_object->type_id == \App\Models\Wiki\WikiType::PAGE)
            @text(title $revision) = Page Title
        @elseif ($revision->wiki_object->type_id == \App\Models\Wiki\WikiType::UPLOAD)
            @file(file) = Choose File (leave blank to keep the existing file)
        @endif
        @textarea(content_text $revision) = Page Content
        <div class="form-group">
            <h4>
                Page preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div id="preview-panel" class="well bbcode"></div>
        </div>
        @text(message) = Description of Edit
        @submit = Edit Page
    @endform
@endsection

@section('scripts')
    <script type="text/javascript">
        $('#update-preview').click(function() {
            $('#preview-panel').html('Loading...');
            $.post('{{ url("api/format") }}?field=content_text', $('form').serializeArray(), function(data) {
                $('#preview-panel').html(data);
            });
        });
    </script>
@endsection
