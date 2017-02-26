@title('Edit forum post by '.$post->user->name)
@extends('app')

@section('content')
    <hc>
        <h1>Edit post by @avatar($post->user inline)</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
            <li><a href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a></li>
            <li class="active">Edit Post</li>
        </ol>
    </hc>
    @form(post/edit)
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
            <div class="card"><div id="preview-panel" class="card-block bbcode"></div></div>
        </div>
        @submit = Edit Post
    @endform
@endsection

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