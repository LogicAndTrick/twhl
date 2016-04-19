@title('Delete Message Thread: '.$thread->subject)
@extends('app')

@section('content')
    <hc>
        <h1>Delete Message Thread: {{ $thread->subject }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('panel', 'index') }}">Control Panel</a></li>
            <li><a href="{{ act('message', 'index') }}">Private Messages</a></li>
            <li><a href="{{ act('message', 'view', $thread->id) }}">View Message</a></li>
            <li class="active">Delete Message</li>
        </ol>
    </hc>
    <p>Are you sure you want to remove this message thread from your list?</p>
    <p>If another user replies to the thread, it will re-appear in the list.</p>
    @form(message/delete)
        @hidden(id $thread)
        @submit = Remove message thread
    @endform

@endsection