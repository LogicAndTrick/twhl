@title('Poll: '.$poll->title)
@extends('app')

@section('content')
    <hc>
        @if (permission('PollAdmin'))
            <a href="{{ act('poll', 'delete', $poll->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Delete</a>
            <a href="{{ act('poll', 'edit', $poll->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
        @endif
        <h1>{{ $poll->title }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('poll', 'index') }}">Polls</a></li>
            <li class="active">View Poll</li>
        </ol>
    </hc>
    <div>
        <div class="media media-panel">
            <div class="media-body">
                <div class="media-heading">
                    @date($poll->created_at) &bull; {{ $poll->isOpen() ? 'Voting now!' : 'Voting closed' }}
                </div>
                <div class="bbcode">{!! $poll->content_html !!}</div>
                <div class="well well-sm">
                    @if ($poll->isOpen() && !$user_vote && Auth::user())
                        @include('polls/_form', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                    @else
                        @include('polls/_results', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                    @endif
                </div>
            </div>
        </div>
    </div>
    @include('comments.list', [ 'article' => $poll, 'comments' => $comments, 'article_type' => \App\Models\Comments\Comment::POLL, 'article_id' => $poll->id ])
@endsection