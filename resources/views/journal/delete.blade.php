@title('Delete Journal Post')
@extends('app')

@section('content')
    <hc>
        <h1>Delete Journal Post #{{ $journal->id }} by @avatar($journal->user inline)</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('journal', 'index') }}">Journals</a></li>
            <li><a href="{{ act('journal', 'view', $journal->id) }}">Journal #{{ $journal->id }}</a></li>
            <li class="active">Delete Journal</li>
        </ol>
    </hc>
    @form(journal/delete)
        <p>Are you sure you want to delete this journal?</p>
        @hidden(id $journal)
        @submit = Delete Journal
    @endform
@endsection