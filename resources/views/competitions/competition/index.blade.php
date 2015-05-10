@extends('app')

@section('content')
    @if (permission('CompetitionAdmin'))
        <a href="{{ act('competition-admin', 'create') }}">Create new Competition</a> |
        <a href="{{ act('competition-restriction', 'index') }}">Modify Competition Restrictions</a>
    @endif

    <h2>Competitions</h2>
    <ul>
        <li>//</li>
    </ul>
@endsection
