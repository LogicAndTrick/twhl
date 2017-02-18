@title('Message thread: '.$thread->subject)
@extends('app')

@section('content')

    <h1>
        Message thread: {{ $thread->subject }}
        <a href="{{ act('message', 'delete', $thread->id) }}" class="btn btn-danger btn-xs"><span class="fa fa-remove"></span> Delete</a>
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('panel', 'index') }}">Control Panel</a></li>
        <li><a href="{{ act('message', 'index') }}">Private Messages</a></li>
        <li class="active">View Message</li>
    </ol>

    <div class="message-thread">
        @foreach ($thread->messages->sortBy('created_at') as $message)
            {? $expand = $message->id == $thread->last_message_id || array_search($message->id, $unread) !== false; ?}
            <div class="message">
                <div class="sender-info {{ $expand ? '' : 'collapsed' }}" data-toggle="collapse" data-target="#message-{{ $message->id }}">
                    @avatar($message->user inline)
                    @date($message->created_at)
                    <span class="collapsed-only"></span>
                </div>
                <div class="message-content collapse {{ $expand ? 'show' : '' }}" id="message-{{ $message->id }}">
                    <div class="bbcode">{!! $message->content_html !!}</div>
                </div>
            </div>
            <script type="text/javascript">
                $('[data-target="#message-{{ $message->id }}"] .collapsed-only').text($('#message-{{ $message->id }}').text());
            </script>
        @endforeach
    </div>

    <h2>Post a Reply</h2>

    <ul class="inline-bullet message-thread-participants">
        <li>Thread participants</li>
        @foreach ($thread->participants as $p)
            <li>@avatar($p inline)</li>
        @endforeach
    </ul>
    @form(message/send)
        @hidden(id $thread)
        @autocomplete(users[] api/users multiple=true) = Invite additional users to this thread
        @textarea(content_text) = Message content
        <div class="form-group">
            <h4>
                Message preview
                <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
            </h4>
            <div id="preview-panel" class="card card-block bbcode"></div>
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