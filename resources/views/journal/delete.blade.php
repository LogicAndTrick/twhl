@title('Delete journal post')
@extends('app')

@section('content')
    <h1>Delete journal: {{ $journal->getTitle() }} by @avatar($journal->user inline)</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('journal', 'index') }}">Journals</a></li>
        <li><a href="{{ act('journal', 'view', $journal->id) }}">Journal #{{ $journal->id }}</a></li>
        <li class="active">Delete Journal</li>
    </ol>

    @form(journal/delete)
        <p>Are you sure you want to delete this journal?</p>
        @hidden(id $journal)
        @submit = Delete Journal
    @endform
@endsection