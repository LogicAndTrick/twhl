@extends('app')

@section('content')
    @form(news/create)
        <h3>Create News Post</h3>
        @text(title) = News Post Title
        @textarea(text) = News Post Content
        <div class="form-group">
            <h4>
                News post preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div id="preview-panel" class="well"></div>
        </div>
        @submit
    @endform
@endsection

@section('scripts')
    <script type="text/javascript">
        $('#update-preview').click(function() {
            $('#preview-panel').html('Loading...');
            $.post('{{ url("api/format") }}?field=text', $('form').serializeArray(), function(data) {
                $('#preview-panel').html(data);
            });
        });
    </script>
@endsection