@title('Create news post')
@extends('app')

@section('content')
    <hc>
        <h1>Create news post</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('news', 'index') }}">News</a></li>
            <li class="active">Create News</li>
        </ol>
    </hc>
    @form(news/create)
        @text(title) = News Post Title
        @textarea(text) = News Post Content
        <div class="form-group">
            <h4>
                News post preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div id="preview-panel" class="well bbcode"></div>
        </div>
        @submit = Create News Post
    @endform
@endsection

@section('scripts')
    <script type="text/javascript">
        $('#update-preview').click(function() {
            $('#preview-panel').html('Loading...');
            $.post('{{ url("api/posts/format") }}?field=text', $('form').serializeArray(), function(data) {
                $('#preview-panel').html(data);
            });
        });
    </script>
@endsection