@title('Publish competition results: '.$comp->name)
@extends('app')

@section('content')
    <h1>Publish results: {{ $comp->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
        <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
        <li><a href="{{ act('competition-judging', 'view', $comp->id) }}">Judging Panel</a></li>
        <li class="active">Publish Results</li>
    </ol>

    <p>
        Publishing the results will set the competition status to <strong>Closed</strong>
        and the results will be visible to all users. Are you sure you want to continue?
    </p>
    @form(competition-judging/publish)
        @hidden(id $comp)
        @submit = Publish Results
    @endform
@endsection