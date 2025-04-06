@title('Delete Comment')
@extends('app')

@section('content')
    <h1>
        Delete comment by
        @if ($comment->user)
            @avatar($comment->user inline)
        @else
            [nobody]
        @endif
    </h1>

    <p>Are you sure you want to delete this comment?</p>
    <div class="bbcode {{$comment->user->getClasses()}}">{!! $comment->content_html !!}</div>
    @form(comment/delete)
        @hidden(id $comment)
        @submit = Delete Comment
    @endform
@endsection