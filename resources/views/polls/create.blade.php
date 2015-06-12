@extends('app')

@section('content')
    <h2>Create Poll</h2>
    @form(poll/create)
        @text(title) = Poll Title
        @text(close_date) = Close Date (dd/mm/yyyy)
        @textarea(content_text class=small) = Poll Description (don't put the items in here)
        @textarea(items class=small) = Poll Items (one per line)
        @submit = Create poll
    @endform
@endsection
