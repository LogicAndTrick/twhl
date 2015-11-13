@extends('app')

@section('content')
    <hc>
        <h1>Restore Deleted Forum: {{ $forum->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            <li class="active">Restore</li>
        </ol>
    </hc>
    @form(forum/restore)
        @hidden(id $forum)
        <p>Restoring this forum will make it visible again. Are you sure?</p>
        @submit = Restore Forum
    @endform
@endsection
