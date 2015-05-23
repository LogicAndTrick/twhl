@extends('app')

@section('content')
    @if (permission('CompetitionAdmin'))
        <a href="{{ act('competition-admin', 'create') }}">Create new Competition</a> |
        <a href="{{ act('competition-restriction', 'index') }}">Modify Competition Restrictions</a>
    @endif

    <h2>Competitions</h2>
    <ul>
        @foreach ($comps->sortByDesc('close_date') as $comp)
        <li>
            <h4><a href="{{ act('competition', $comp->isClosed() ? 'results' : 'brief', $comp->id) }}">{{ $comp->name }}</a></h4>
            <strong>{{ $comp->getStatusText() }}</strong> &bull; {{ $comp->type->name }} &bull; {{ $comp->judge_type->name }}<br/>
            {{ $comp->isActive() ? 'Closes' : 'Closed' }} on {{ $comp->close_date->format('jS F Y') }}
        </li>
        @endforeach
    </ul>
@endsection
