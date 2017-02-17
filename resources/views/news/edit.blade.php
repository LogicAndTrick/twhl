@title('Edit news post: '.$news->title)
@extends('app')

@section('content')
    <hc>
        <h1>Edit news post: {{ $news->title }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('news', 'index') }}">News</a></li>
            <li><a href="{{ act('news', 'view', $news->id) }}">{{ $news->title }}</a></li>
            <li class="active">Edit</li>
        </ol>
    </hc>
    @form(news/edit)
        @hidden(id $news)
        @text(title $news) = News Post Title
        @textarea(content_text:text $news) = News Post Content
        <div class="form-group">
            <h4>
                News post preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div id="preview-panel" class="card card-block bbcode"></div>
        </div>
        @submit = Edit News Post
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