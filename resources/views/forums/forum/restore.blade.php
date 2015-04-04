@extends('app')

@section('content')
    @form(forum/restore)
        <h3>Restore Forum: {{ $forum->name }}</h3>
        @hidden(id $forum)
        <p>Restoring this forum will make it visible again. Are you sure?</p>
        @submit
    @endform
@endsection
