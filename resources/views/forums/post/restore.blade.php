@extends('app')

@section('content')
    @form(post/restore)
        <h3>Restore Post in {{ $forum->name }} / {{ $thread->title }}</h3>
        @hidden(id $post)
        <p>Restoring this post will make it visible again. Are you sure?</p>
        @submit
    @endform
@endsection
