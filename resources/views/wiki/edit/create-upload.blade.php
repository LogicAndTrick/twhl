@title('Upload new wiki file')
@extends('app')

@section('content')
    @include('wiki.nav')
    <hc>
        <h1>Upload new file</h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/wiki') }}">Wiki</a></li>
            <li class="active">Create File</li>
        </ol>
    </hc>
    <div class="alert alert-success">
        <h4>Please obey the rules when uploading files</h4>
        <ul>
            <li>Only files with these extensions can be uploaded: <strong>.jpg, .png, .gif, .mp3, .mp4</strong></li>
            <li>The size limit is <strong>4mb</strong></li>
            @if (permission('Admin'))
                <li>
                    Because you're an admin, you have a bit more freedom:
                    <ul>
                        <li>Extra file extensions: <strong>.zip, .rar</strong></li>
                        <li>Increased size limit: <strong>16mb</strong></li>
                    </ul>
                </li>
            @else
                <li>To upload archive files or items larger than 4mb, please contact an admin to do it for you.</li>
            @endif
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
