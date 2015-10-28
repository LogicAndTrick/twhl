@extends('app')

@section('content')
    @form(post/edit)
        <h3>Edit Post in {{ $forum->name }} / {{ $thread->title }}</h3>
        @hidden(id $post)
        @if (permission('ForumAdmin'))
            @autocomplete(user_id api/users $post) = Post Owner
        @endif
        @textarea(content_text $post) = Post Content
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
            $.post('{{ url("api/format") }}?field=content_text', $('form').serializeArray(), function(data) {
                $('#preview-panel').html(data);
            });
        });
    </script>
@endsection