@extends('app')

@section('content')
    <hc>
        @if ($journal->isEditable())
            <a href="{{ act('journal', 'delete', $journal->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Delete</a>
            <a href="{{ act('journal', 'edit', $journal->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
        @endif
        <h1>Journal Post #{{ $journal->id }} by @include('user._avatar', [ 'class' => 'inline', 'user' => $journal->user ])</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('journal', 'index') }}">Journals</a></li>
            <li class="active">View Journal</li>
        </ol>
    </hc>
    <div class="bbcode">
        {!! $journal->content_html !!}
    </div>
    @include('comments.list', [ 'comments' => $comments, 'article_type' => \App\Models\Comments\Comment::JOURNAL, 'article_id' => $journal->id ])
@endsection