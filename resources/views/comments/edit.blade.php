@extends('app')

@section('content')
    <hc>
        <h1>
            Edit Comment by
            @if ($comment->user)
                @avatar($comment->user inline)
            @else
                [nobody]
            @endif
        </h1>
    </hc>
    @include('comments.create', [ 'article_type' => $comment->article_type, 'article_id' => $comment->article_id, 'text' => $comment->content_text, 'comment' => $comment ])
@endsection