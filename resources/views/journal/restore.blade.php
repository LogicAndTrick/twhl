@extends('app')

@section('content')
    @form(journal/restore)
        <h3>Restore Journal</h3>
        <p>Are you sure you want to restore this journal?</p>
        @hidden(id $journal)
        @submit
    @endform
@endsection