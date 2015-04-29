@extends('app')

@section('content')
    @form(journal/delete)
        <h3>Delete Journal</h3>
        <p>Are you sure you want to delete this journal?</p>
        @hidden(id $journal)
        @submit
    @endform
@endsection