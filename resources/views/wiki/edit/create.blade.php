@extends('app')

@section('content')
    @include('wiki.nav')
    <h2>Create New Page</h2>
    @form(wiki/create)
        @text(title $slug_title) = Page Title
        @textarea(content_text) = Page Content
        <div class="form-group">
            <h4>
                Page preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div id="preview-panel" class="well bbcode"></div>
        </div>
        @submit = Create Page
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