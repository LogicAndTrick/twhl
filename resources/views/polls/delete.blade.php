@extends('app')

@section('content')
    <h2>Delete Poll: {{ $poll->title }}</h2>
    <p>Are you sure you want to delete this poll?</p>
    @form(poll/delete)
        @hidden(id $poll)
        @submit = Delete poll
    @endform
@endsection
