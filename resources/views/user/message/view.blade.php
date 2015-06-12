@extends('app')

@section('content')
    <h2>
        Message Thread: {{ $thread->subject }}
    </h2>
    <div class="message-thread">
        @foreach ($thread->messages->sortBy('created_at') as $message)
            {? $expand = $message->id == $thread->last_message_id || array_search($message->id, $unread) !== false; ?}
            <div class="message">
                <div class="sender-info {{ $expand ? '' : 'collapsed' }}" data-toggle="collapse" data-target="#message-{{ $message->id }}">
                    @include('user._avatar', [ 'user' => $message->user, 'class' => 'inline' ]),
                    {{ $message->created_at->diffForHumans() }}
                    <span class="collapsed-only"></span>
                </div>
                <div class="message-content collapse {{ $expand ? 'in' : '' }}" id="message-{{ $message->id }}">
                    <div class="bbcode">{!! $message->content_html !!}</div>
                </div>
            </div>
            <script type="text/javascript">
                $('[data-target="#message-{{ $message->id }}"] .collapsed-only').text($('#message-{{ $message->id }}').text());
            </script>
        @endforeach
    </div>
    <h3>Post a Reply</h3>
    <ul class="inline-bullet message-thread-participants">
        <li>Thread participants</li>
        @foreach ($thread->participants as $p)
            <li>@include('user._avatar', [ 'user' => $p, 'class' => 'inline' ])</li>
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
            <div id="preview-panel" class="well bbcode"></div>
        </div>
        @submit = Send Message
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