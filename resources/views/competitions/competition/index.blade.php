@extends('app')

@section('content')
    <hc>
        @if (permission('CompetitionAdmin'))
            <a class="btn btn-info btn-xs" href="{{ act('competition-restriction', 'index') }}"><span class="glyphicon glyphicon-pencil"></span> Modify Competition Restrictions</a>
            <a class="btn btn-primary btn-xs" href="{{ act('competition-admin', 'create') }}"><span class="glyphicon glyphicon-plus"></span> Create new Competition</a>
        @endif
        <h1>Competitions</h1>
    </hc>
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
