@title('Create Forum Thread: '.$forum->name)
@extends('app')

@section('content')
    <hc>
        <h1>Create Thread: {{ $forum->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
            <li class="active">Create Thread</li>
        </ol>
    </hc>
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
        @submit = Create Thread
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