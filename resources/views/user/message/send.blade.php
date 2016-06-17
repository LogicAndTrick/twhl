@title('Send private message')
@extends('app')

@section('content')
    <hc>
        <h1>Send private message</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('panel', 'index') }}">Control Panel</a></li>
            <li><a href="{{ act('message', 'index') }}">Private Messages</a></li>
            <li class="active">Send Message</li>
        </ol>
    </hc>
    @form(message/send)
        @autocomplete(users[] api/users $recipients multiple=true) = Message recipients
        @text(subject) = Subject
        @textarea(content_text) = Message content
        <div class="form-group">
            <h4>
                Message preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div id="preview-panel" class="well bbcode"></div>
        </div>
        @submit = Send Message
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