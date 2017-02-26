@title('Restore forum: '.$forum->name)
@extends('app')

@section('content')
    <h1>Restore deleted forum: {{ $forum->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
        <li class="active">Restore</li>
    </ol>

    @form(forum/restore)
        @hidden(id $forum)
        <p>Restoring this forum will make it visible again. Are you sure?</p>
        @submit = Restore Forum
    @endform
@endsection
