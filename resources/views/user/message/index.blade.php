@extends('app')

@section('content')
    <hc>
        @if (Auth::user()->id == $user->id)
            <a href="{{ act('message', 'send') }}" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-envelope"></span> Send New Message</a>
        @endif
        <h1>Messages: {{ $user->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
            <li class="active">Private Messages</li>
        </ol>
        {!! $threads->render() !!}
    </hc>
    <div class="alert alert-info">
        Messages allow you to have private conversations with other TWHL members.
        You can even invite multiple people to join your conversation.
        It's basically the same as email, except less convenient! Enjoy!
    </div>
    </h2>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Last Message</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($threads as $thread)
                {? $is_unread = array_search($thread->id, $unread) !== false; ?}
                <tr class="{{ $is_unread ? 'unread' : '' }}">
                    <td>
                        @if ($is_unread)
                            <span class="glyphicon glyphicon-exclamation-sign"></span>
                        @endif
                        <a href="{{ act('message', 'view', $thread->id) }}">{{ $thread->subject }}</a>
                    </td>
                    <td>
                        @date($thread->last_message->created_at) by
                        @avatar($thread->last_message->user inline)
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer-container">
        {!! $threads->render() !!}
    </div>
@endsection
