@extends('app')

@section('content')
    <h2>Create Thread: {{ $forum->name }}</h2>
    @form(thread/create)
        <input type="hidden" name="forum_id" value="{{ $forum->id }}" />
        @text(title) = Thread Title
        @textarea(text) = Post Content
        <div class="form-group">
            <h4>
                Post preview
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