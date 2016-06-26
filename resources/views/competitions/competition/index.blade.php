@title('Competitions')
@extends('app')

@section('content')
    <hc>
        @if (permission('CompetitionAdmin'))
            <a class="btn btn-info btn-xs" href="{{ act('competition-restriction', 'index') }}"><span class="glyphicon glyphicon-pencil"></span> Modify Competition Restrictions</a>
            <a class="btn btn-primary btn-xs" href="{{ act('competition-admin', 'create') }}"><span class="glyphicon glyphicon-plus"></span> Create new Competition</a>
        @endif
        <h1>Competitions</h1>
    </hc>
    <ul class="media-list competition-list">
        @foreach ($comps->sortByDesc('close_date') as $comp)
        <li class="media media-panel">
            <div class="media-body">
                <div class="media-heading">
                    <h2>
                        <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a>
                        <small class="pull-right">{{ $comp->type->name }} &bull; {{ $comp->judge_type->name }}</small>
                    </h2>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-lg-push-8 col-md-5 col-md-push-7 info-column">
                        <div class="text-center">
                            <h2>{{ $comp->getStatusText() }}</h2>

                            @if ($comp->isVotingOpen())
                                <a href="{{ act('competition', 'vote', $comp->id) }}" class="btn btn-success">{{ $comp->canVote() ? 'Vote Now' : 'View Entries' }}</a>
                            @elseif ($comp->isJudging() && $comp->canJudge())
                                <a href="{{ act('competition-judging', 'view', $comp->id) }}" class="btn btn-inverse"><span class="glyphicon glyphicon-eye-open"></span> Go to Judging Panel</a>
                            @elseif ($comp->isClosed())
                                <div class="competition-winners">
                                    <h5>Winners</h5>
                                    @foreach ($comp->getEntriesForWinners() as $entry)
                                        {? $result = $comp->results->where('entry_id', $entry->id)->first(); ?}
                                        @avatar($entry->user small show_border=true show_name=false)
                                    @endforeach
                                </div>
                            @else
                                {{ $comp->isActive() ? 'Closes' : 'Closed' }} @date($comp->close_date) ({{ $comp->close_date->format('jS F Y') }})
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-8 col-lg-pull-4 col-md-7 col-md-pull-5 brief-column">
                        <div class="bbcode">{!! $comp->brief_html !!}</div>
                    </div>
                </div>
            </div>
        </li>
        @endforeach
    </ul>
@endsection
