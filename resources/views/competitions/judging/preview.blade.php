@title('Preview competition results: '.$comp->name)
@extends('app')

@section('content')
    <h1>
        Preview results: {{ $comp->name }}

        @if (permission('CompetitionAdmin') && $comp->isJudging())
            <a href="{{ act('competition-judging', 'publish', $comp->id) }}" class="btn btn-outline-info btn-xs"><span class="fa fa-arrow-right"></span> Publish Results</a>
        @endif
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
        <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
        <li><a href="{{ act('competition-judging', 'view', $comp->id) }}">Judging Panel</a></li>
        <li class="active">Preview Results</li>
    </ol>

    @include('competitions.competition._results', [ 'comp' => $comp ])

@endsection

@section('scripts')
    @include('competitions._gallery_javascript')
@endsection