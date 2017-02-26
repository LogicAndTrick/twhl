@title('Add competition entry: '.$comp->name)
@extends('app')

@section('content')
    <h1>Add competition entry: {{ $comp->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
        <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
        <li><a href="{{ act('competition-judging', 'view', $comp->id) }}">Judging Panel</a></li>
        <li class="active">Create Entry</li>
    </ol>

    @form(competition-judging/create-entry upload=true)
        @autocomplete(user_id api/users) = User
        @include('competitions.entry._entry-form-fields', [ 'comp' => $comp, 'entry' => null ])
    @endform
@endsection