@title('Delete Comment')
@extends('app')

@section('content')
    <hc>
        <h1>
            Delete Comment by
            @if ($comment->user)
                @avatar($comment->user inline)
            @else
                [nobody]
            @endif
        </h1>
    </hc>
    <p>Are you sure you want to delete this comment?</p>
    <div class="bbcode">{!! $comment->content_html !!}</div>
    @form(comment/delete)
        @hidden(id $comment)
        @submit = Delete Comment
    @endform
@endsection