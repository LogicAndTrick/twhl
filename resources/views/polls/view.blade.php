@extends('app')

@section('content')
    <h2>
        {{ $poll->title }}
        @if (permission('PollAdmin'))
            <a href="{{ act('poll', 'delete', $poll->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Delete</a>
            <a href="{{ act('poll', 'edit', $poll->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
        @endif
    </h2>
    <div class="row">
        <div class="col-md-4 col-lg-6">
            <div class="bbcode">{!! $poll->content_html !!}</div>
        </div>
        <div class="col-md-8 col-lg-6">
            <div class="well well-sm">
                @if ($poll->isOpen() && !$user_vote && Auth::user())
                    @include('polls/_form', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                @else
                    @include('polls/_results', [ 'poll' => $poll, 'user_votes' => $user_votes ])
                @endif
            </div>
        </div>
    </div>
    @include('comments.list', [ 'comments' => $comments, 'article_type' => \App\Models\Comments\Comment::POLL, 'article_id' => $poll->id ])
@endsection

@section('scripts')
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js"></script>
@endsection