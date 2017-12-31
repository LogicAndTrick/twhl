@title('Private messages')
@extends('app')

@section('content')

    <h1>
        <span class="fa fa-envelope"></span>
        Messages: {{ $user->name }}
        @if (Auth::user()->id == $user->id)
            <a href="{{ act('message', 'send') }}" class="btn btn-outline-info btn-xs"><span class="fa fa-envelope"></span> Send New Message</a>
        @endif
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
        <li class="active">Private Messages</li>
    </ol>

    {!! $threads->render() !!}

    <div class="alert alert-info">
        Messages allow you to have private conversations with other TWHL members.
        You can even invite multiple people to join your conversation.
        It's basically the same as email, except less convenient! Enjoy!
    </div>

    <table class="table table-striped table-bordered message-list">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Last Message</th>
                <th class="col-mod"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($threads as $thread)
                {? $is_unread = array_search($thread->id, $unread) !== false; ?}
                <tr class="{{ $is_unread ? 'unread' : '' }}">
                    <td>
                        @if ($is_unread)
                            <span class="fa fa-exclamation-triangle"></span>
                        @endif
                        <a href="{{ act('message', 'view', $thread->id) }}">{{ $thread->subject }}</a>
                    </td>
                    <td>
                        @date($thread->last_message->created_at) by
                        @avatar($thread->last_message->user inline)
                    </td>
                    <td class="col-mod">
                        <a href="{{ act('message', 'delete', $thread->id) }}" class="btn btn-danger btn-xs"><span class="fa fa-remove"></span></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {!! $threads->render() !!}

@endsection
