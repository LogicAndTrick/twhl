@extends('app')

@section('content')
    <h2>
        @if ($journal->isEditable())
            <a href="{{ act('journal', 'delete', $journal->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Delete</a>
            <a href="{{ act('journal', 'edit', $journal->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
        @endif
    </h2>
    <div class="bbcode">
        {!! $journal->content_html !!}
    </div>
    @include('comments.list', [ 'comments' => $comments, 'article_type' => \App\Models\Comments\Comment::JOURNAL, 'article_id' => $journal->id ])
@endsection