@extends('app')

@section('content')
    <h3>Edit Entry</h3>
    @form(competition-judging/edit-entry upload=true)
        @hidden(user_id $user)
        @include('competitions.entry._entry-form-fields', [ 'comp' => $comp, 'entry' => $entry ])
    @endform
@endsection