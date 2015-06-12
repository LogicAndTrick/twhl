@extends('app')

@section('content')
    <div class="alert alert-info">
        Messages allow you to have private conversations with other TWHL members.
        You can even invite multiple people to join your conversation.
        It's basically the same as email, except less convenient! Enjoy!
    </div>
    <h2>
        Messages: {{ $user->name }}
        @if (Auth::user()->id == $user->id)
            <a href="{{ act('message', 'send') }}" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-envelope"></span> Send New Message</a>
        @endif
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
                {? $unread = array_search($thread->id, $unread) !== false; ?}
                <tr class="{{ $unread ? 'unread' : '' }}">
                    <td>
                        @if ($unread)
                            <span class="glyphicon glyphicon-exclamation-sign"></span>
                        @endif
                        <a href="{{ act('message', 'view', $thread->id) }}">{{ $thread->subject }}</a>
                    </td>
                    <td>
                        {{ $thread->last_message->created_at->diffForHumans() }} by
                        @include('user._avatar', [ 'user' => $thread->last_message->user, 'class' => 'inline' ])
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {!! $threads->render() !!}

@endsection
