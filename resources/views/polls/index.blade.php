@title('Polls')
@extends('app')

@section('content')
    <hc>
        @if (permission('PollAdmin'))
            <a class="btn btn-primary btn-xs" href="{{ act('poll', 'create') }}"><span class="fa fa-plus"></span> Create new poll</a>
        @endif
        <h1>Polls</h1>
        {!! $polls->render() !!}
    </hc>
    <ul class="media-list">
        @foreach ($polls as $poll)
            <li class="media media-panel" id="poll-{{ $poll->id }}">
                <div class="media-body">
                    <div class="media-heading">
                        @if (permission('PollAdmin'))
                            <a href="{{ act('poll', 'delete', $poll->id) }}" class="btn btn-danger btn-xs"><span class="fa fa-remove"></span> Delete</a>
                            <a href="{{ act('poll', 'edit', $poll->id) }}" class="btn btn-primary btn-xs"><span class="fa fa-pencil"></span> Edit</a>
                        @endif
                        <h2>
                            <a href="{{ act('poll', 'view', $poll->id) }}">{{ $poll->title }}</a>
                        </h2>
                        @date($poll->created_at) &bull; {{ $poll->isOpen() ? 'Voting now!' : 'Voting closed' }}
                    </div>
                    <div class="bbcode">{!! $poll->content_html !!}</div>
                    <div class="well well-sm">
                        @if ($poll->isOpen() && Auth::user() && array_search($poll->id, $user_polls) === false)
                            @include('polls/_form', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                        @else
                            @include('polls/_results', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                        @endif
                    </div>
                    <div class="media-footer">
                        <a href="{{ act('poll', 'view', $poll->id) }}" class="btn btn-xs btn-link link">
                            <span class="fa fa-comment"></span>
                            {{ $poll->stat_comments }} comment{{$poll->stat_comments==1?'':'s'}}
                        </a>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
@endsection