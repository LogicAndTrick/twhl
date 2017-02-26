@title('Create journal post')
@extends('app')

@section('content')
    <hc>
        <h1>Create journal post</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('journal', 'index') }}">Journals</a></li>
            <li class="active">Create Journal</li>
        </ol>
    </hc>
    @form(journal/create)
        @text(title) = Journal Title
        @textarea(text) = Journal Content
        <div class="form-group">
            <h4>
                Journal preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div class="card"><div id="preview-panel" class="card-block bbcode"></div></div>
        </div>
        @submit = Create Journal
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