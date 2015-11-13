@extends('app')

@section('content')
    <hc>
        @if (permission('PollAdmin'))
            <a class="btn btn-primary btn-xs" href="{{ act('poll', 'create') }}">Create new poll</a>
        @endif
        <h1>Polls</h1>
        {!! $polls->render() !!}
    </hc>
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
                    @if ($poll->isOpen() && Auth::user() && array_search($poll->id, $user_polls) === false)
                        @include('polls/_form', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                    @else
                        @include('polls/_results', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js"></script>
@endsection