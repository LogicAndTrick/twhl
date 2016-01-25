@title('Delete Competition Entry: '.$entry->name)
@extends('app')

@section('content')
    <hc>
        <h1>Delete Competition Entry: {{ $entry->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
            <li class="active">Delete Entry</li>
        </ol>
    </hc>
    @form(competition-entry/delete)
        @hidden(id $entry)
        <p>Are you sure you want to delete this competition entry?</p>
        <div class="well">
            @include('competitions.entry._entry', [ 'comp' => $comp, 'entry' => $entry, 'deleting' => true ])
            @include('competitions._gallery_javascript')
        </div>
        @submit = Delete Entry
    @endform
@endsection
