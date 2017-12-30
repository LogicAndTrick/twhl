@title('Send private message')
@extends('app')

@section('content')

    <h1>Send private message</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('panel', 'index') }}">Control Panel</a></li>
        <li><a href="{{ act('message', 'index') }}">Private Messages</a></li>
        <li class="active">Send Message</li>
    </ol>

    @form(message/send)
        @autocomplete(users[] api/users $recipients multiple=true) = Message recipients
        @text(subject) = Subject
        <div class="wikicode-input">
            @textarea(content_text) = Message content
        </div>
        @submit = Send Message
    @endform
@endsection
