@title('Polls')
@extends('app')

@section('content')
    
    <h1>
        <span class="fa fa-pie-chart"></span>
        Polls
        @if (permission('PollAdmin'))
            <a class="btn btn-outline-primary btn-xs" href="{{ act('poll', 'create') }}"><span class="fa fa-plus"></span> Create new poll</a>
        @endif
    </h1>

    {!! $polls->render() !!}

    <div class="poll-list">
        @foreach ($polls as $poll)
            <div class="slot" id="poll-{{ $poll->id }}">
                <div class="slot-heading">
                    <div class="slot-title">
                        <a href="{{ act('poll', 'view', $poll->id) }}">{{ $poll->title }}</a>
                        @if (permission('PollAdmin'))
                            <a href="{{ act('poll', 'delete', $poll->id) }}" class="btn btn-outline-danger btn-xs"><span class="fa fa-remove"></span> Delete</a>
                            <a href="{{ act('poll', 'edit', $poll->id) }}" class="btn btn-outline-primary btn-xs"><span class="fa fa-pencil"></span> Edit</a>
                        @endif
                    </div>
                    <div class="slot-subtitle">
                        Posted @date($poll->created_at) &bull;
                        {{ $poll->isOpen() ? 'Voting now!' : 'Voting closed' }} &bull;
                        <a href="{{ act('poll', 'view', $poll->id) }}">
                            <span class="fa fa-comment"></span>
                            {{ $poll->stat_comments }} comment{{$poll->stat_comments==1?'':'s'}}
                        </a>
                    </div>
                </div>
                <div class="slot-main">
                    <div class="bbcode">{!! $poll->content_html !!}</div>
                    <div class="card card-block">
                        @if ($poll->isOpen() && Auth::user() && array_search($poll->id, $user_polls) === false)
                            @include('polls/_form', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                        @else
                            @include('polls/_results', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {!! $polls->render() !!}
@endsection