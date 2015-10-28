@extends('app')

@section('content')
    @include('wiki.nav')
    <h2>Upload New File</h2>
    <div class="alert alert-success">
        <h4>Please obey the rules when uploading files</h4>
        <ul>
            <li>Only files with these extensions can be uploaded: <strong>.jpg, .png, .gif</strong></li>
            <li>The size limit is <strong>4mb</strong></li>
            <li>Do not upload any copyrighted or inappropriate content</li>
        </ul>
    </div>
    @form(wiki/create-upload upload=true)
        @text(title $slug_title) = File Name
        @file(file) = Choose File
        @textarea(content_text) = File Details
        <div class="form-group">
            <h4>
                Page preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div id="preview-panel" class="well bbcode"></div>
        </div>
        @submit = Upload File
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
