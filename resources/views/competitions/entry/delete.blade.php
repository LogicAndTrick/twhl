@extends('app')

@section('content')
    <h2>Remove Competition Entry </h2>
    @form(competition-entry/delete)
        @hidden(id $entry)
        <p>Are you sure you want to remove this competition entry?</p>
        <div class="well">
            @include('competitions.entry._entry', [ 'comp' => $comp, 'entry' => $entry, 'deleting' => true ])
        </div>
        @submit = Remove Entry
    @endform
@endsection
