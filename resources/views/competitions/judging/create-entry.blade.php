@title('Add Competition Entry: '.$comp->name)
@extends('app')

@section('content')
    <hc>
        <h1>Add Competition Entry: {{ $comp->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
            <li><a href="{{ act('competition-judging', 'view', $comp->id) }}">Judging Panel</a></li>
            <li class="active">Create Entry</li>
        </ol>
    </hc>
    @form(competition-judging/create-entry upload=true)
        @autocomplete(user_id api/users) = User
        @include('competitions.entry._entry-form-fields', [ 'comp' => $comp, 'entry' => null ])
    @endform
@endsection