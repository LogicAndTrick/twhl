<hc>
    <h1>Page not found: {{ $slug }}</h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/wiki') }}">Wiki</a></li>
        <li class="active">Nonexistent Page</li>
    </ol>
</hc>
<p>
    This page doesn't exist on the Wiki.
</p>
@if (permission('WikiCreate'))
    <p>You can create it if you think it is missing.</p>
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
@endif

@section('scripts')
    <script type="text/javascript">
        $('#update-preview').click(function() {
            $('#preview-panel').html('Loading...');
            $.post('{{ url("api/posts/format") }}?field=content_text', $('form').serializeArray(), function(data) {
                $('#preview-panel').html(data);
            });
        });
    </script>
@endsection
