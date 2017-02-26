@title('Edit competition entry: '.$comp->name)
@extends('app')

@section('content')
    <h1>Edit competition entry: {{ $comp->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
        <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
        <li><a href="{{ act('competition-judging', 'view', $comp->id) }}">Judging Panel</a></li>
        <li class="active">Edit Entry</li>
    </ol>

    @form(competition-judging/edit-entry upload=true)
        @hidden(user_id $entry)
        @include('competitions.entry._entry-form-fields', [ 'comp' => $comp, 'entry' => $entry ])
    @endform
@endsection