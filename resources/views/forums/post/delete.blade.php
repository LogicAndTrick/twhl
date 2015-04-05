@extends('app')

@section('content')
    @form(post/delete)
        <h3>Delete Post in {{ $forum->name }} / {{ $thread->title }}</h3>
        @hidden(id $post)
        <p>You are about to delete a post. Are you sure?</p>
        @submit
    @endform
@endsection
