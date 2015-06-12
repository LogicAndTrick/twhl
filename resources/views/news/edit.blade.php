@extends('app')

@section('content')
    @form(news/edit)
        <h3>Edit News Post: {{ $news->title }}</h3>
        @hidden(id $news)
        @text(title $news) = News Post Title
        @textarea(content_text:text $news) = News Post Content
        <div class="form-group">
            <h4>
                News post preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div id="preview-panel" class="well bbcode"></div>
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