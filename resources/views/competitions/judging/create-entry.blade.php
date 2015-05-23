@extends('app')

@section('content')
    <h3>Add Entry</h3>
    @form(competition-judging/create-entry upload=true)
        @autocomplete(user_id api/users) = User
        @include('competitions.entry._entry-form-fields', [ 'comp' => $comp, 'entry' => null ])
    @endform
@endsection