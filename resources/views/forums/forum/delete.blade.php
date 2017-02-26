@title('Delete forum: '.$forum->name)
@extends('app')

@section('content')
    <h1>Delete forum {{ $forum->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
        <li class="active">Delete</li>
    </ol>

    @form(forum/delete)
        @hidden(id $forum)
        <p>Deleting a forum is probably not a great idea. Are you sure?</p>
        @submit = Delete Forum
    @endform
@endsection
