@title('Poll: '.$poll->title)
@extends('app')

<?php
    $meta_description = $poll->content_text;
?>

@section('content')
    <h1>
        {{ $poll->title }}
        @if (permission('PollAdmin'))
            <a href="{{ act('poll', 'delete', $poll->id) }}" class="btn btn-outline-danger btn-xs"><span class="fa fa-remove"></span> Delete</a>
            <a href="{{ act('poll', 'edit', $poll->id) }}" class="btn btn-outline-primary btn-xs"><span class="fa fa-pencil"></span> Edit</a>
        @endif
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('poll', 'index') }}">Polls</a></li>
        <li class="active">View Poll</li>
    </ol>

    <div class="slot">
        <div class="slot-heading">
            <div class="slot-subtitle">
                Posted @date($poll->created_at) &bull; {{ $poll->isOpen() ? 'Voting now!' : 'Voting closed' }}
            </div>
        </div>
        <div class="slot-row">
            <div class="slot-main">
                <div class="bbcode">{!! $poll->content_html !!}</div>
                <div class="card card-body">
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