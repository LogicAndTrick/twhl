@extends('app')

@section('content')
    <h2>Edit Comment</h2>
    @include('comments.create', [ 'article_type' => $comment->article_type, 'article_id' => $comment->article_id, 'text' => $comment->content_text, 'comment' => $comment ])
@endsection