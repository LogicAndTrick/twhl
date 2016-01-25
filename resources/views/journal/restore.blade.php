@title('Restore Journal Post')
@extends('app')

@section('content')
    <hc>
        <h1>Restore Journal Post #{{ $journal->id }} by @avatar($journal->user inline)</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('journal', 'index') }}">Journals</a></li>
            <li><a href="{{ act('journal', 'view', $journal->id) }}">Journal #{{ $journal->id }}</a></li>
            <li class="active">Restore Journal</li>
        </ol>
    </hc>
    @form(journal/restore)
        <p>Are you sure you want to restore this journal?</p>
        @hidden(id $journal)
        @submit = Restore Journal
    @endform
@endsection