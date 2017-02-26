@title('Edit Comment')
@extends('app')

@section('content')
    <h1>
        Edit comment by
        @if ($comment->user)
            @avatar($comment->user inline)
        @else
            [nobody]
        @endif
    </h1>

    @include('comments.create', [ 'article_type' => $comment->article_type, 'article_id' => $comment->article_id, 'text' => $comment->content_text, 'comment' => $comment ])
@endsection