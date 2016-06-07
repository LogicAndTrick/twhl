@title('Create new wiki page')
@extends('app')

@section('content')
    @include('wiki.nav')
    <hc>
        <h1>Create new page</h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/wiki') }}">Wiki</a></li>
            <li class="active">Create Page</li>
        </ol>
    </hc>
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