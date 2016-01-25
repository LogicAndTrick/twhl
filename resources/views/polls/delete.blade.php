@title('Delete Poll: '.$poll->title)
@extends('app')

@section('content')
    <hc>
        <h1>Delete Poll: {{ $poll->title }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('poll', 'index') }}">Polls</a></li>
            <li><a href="{{ act('poll', 'view', $poll->id) }}">{{ $poll->title }}</a></li>
            <li class="active">Delete Poll</li>
        </ol>
    </hc>
    <p>Are you sure you want to delete this poll?</p>
    @form(poll/delete)
        @hidden(id $poll)
        @submit = Delete poll
    @endform
@endsection
