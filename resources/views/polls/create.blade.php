@title('Create poll')
@extends('app')

@section('content')
    <h1>Create poll</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('poll', 'index') }}">Polls</a></li>
        <li class="active">Create Poll</li>
    </ol>

    @form(poll/create)
        @text(title) = Poll Title
        @text(close_date) = Close Date (dd/mm/yyyy)
        @textarea(content_text class=small) = Poll Description (don't put the items in here)
        @textarea(items class=small) = Poll Items (one per line)
        @submit = Create poll
    @endform
@endsection
