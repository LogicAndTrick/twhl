@extends('app')

@section('content')
    <h2>
        Polls
        @if (permission('PollAdmin'))
            <a class="btn btn-primary btn-xs" href="{{ act('poll', 'create') }}">Create new poll</a>
        @endif
    </h2>
    @foreach ($polls as $poll)
        <h2>
            <a href="{{ act('poll', 'view', $poll->id) }}">{{ $poll->title }}</a>
            @if ($poll->isOpen())
                <small>Voting now!</small>
            @endif
        </h2>
        <div class="row">
            <div class="col-md-4 col-lg-6">
                <div class="bbcode">{!! $poll->content_html !!}</div>
            </div>
            <div class="col-md-8 col-lg-6">
                <div class="well well-sm">
                    @if ($poll->isOpen())
                        @include('polls/_form', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                    @else
                        @include('polls/_results', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                    @endif
                </div>
            </div>
        </div>
    @endforeach
    {!! $polls->render() !!}
@endsection