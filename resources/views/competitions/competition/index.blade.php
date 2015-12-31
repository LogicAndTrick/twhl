@extends('app')

@section('content')
    <hc>
        @if (permission('CompetitionAdmin'))
            <a class="btn btn-info btn-xs" href="{{ act('competition-restriction', 'index') }}"><span class="glyphicon glyphicon-pencil"></span> Modify Competition Restrictions</a>
            <a class="btn btn-primary btn-xs" href="{{ act('competition-admin', 'create') }}"><span class="glyphicon glyphicon-plus"></span> Create new Competition</a>
        @endif
        <h1>Competitions</h1>
    </hc>
    <ul class="media-list">
        @foreach ($comps->sortByDesc('close_date') as $comp)
        <li class="media media-panel">
            <div class="media-body">
                <div class="media-heading">
                    <h2><a href="{{ act('competition', $comp->isClosed() ? 'results' : 'brief', $comp->id) }}">{{ $comp->name }}</a></h2>
                    <strong>{{ $comp->getStatusText() }}</strong> &bull;
                    {{ $comp->type->name }} &bull;
                    {{ $comp->judge_type->name }}
                </div>
                {{ $comp->isActive() ? 'Closes' : 'Closed' }} @date($comp->close_date) ({{ $comp->close_date->format('jS F Y') }})
                @if ($comp->isClosed())
                    <div class="competition-winners">
                        <h4>Winners</h4>
                        @foreach ($comp->getEntriesForWinners() as $entry)
                            {? $result = $comp->results->where('entry_id', $entry->id)->first(); ?}
                            @avatar($entry->user small show_border=true show_name=false)
                        @endforeach
                    </div>
                @endif
            </div>
        </li>
        @endforeach
    </ul>
@endsection
