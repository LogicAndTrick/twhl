@extends('app')

@section('content')
    <h2>Delete Comment</h2>
    <p>Are you sure you want to delete this comment by <strong>{{ $comment->user->name }}</strong>?</p>
    <div class="bbcode">{!! $comment->content_html !!}</div>
    @form(comment/delete)
        @hidden(id $comment)
        @submit = Delete Comment
    @endform
@endsection