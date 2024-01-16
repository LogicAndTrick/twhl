@title('Competitions')
@extends('app')

@section('content')
    <h1>
        <span class="fa fa-trophy"></span>
        Competitions
        @if (permission('CompetitionAdmin'))
            <a class="btn btn-outline-info btn-xs" href="{{ act('competition-restriction', 'index') }}"><span class="fa fa-pencil"></span> Modify Competition Restrictions</a>
            <a class="btn btn-outline-primary btn-xs" href="{{ act('competition-admin', 'create') }}"><span class="fa fa-plus"></span> Create new Competition</a>
        @endif
    </h1>
    <div class="competition-list">
        @foreach ($comps->sortByDesc('close_date') as $comp)
        <div class="slot">
            <div class="slot-heading">
                <small class="pull-right">{{ $comp->type->name }} &bull; {{ $comp->judge_type->name }}</small>
                <div class="slot-title">
                    <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a>
                </div>
            </div>
            <div class="slot-main">
                <div class="row">
                    <div class="col-xl-4 col-lg-5 order-2 info-column">
                        <h2>{{ $comp->getStatusText() }}</h2>

                        @if ($comp->isVotingOpen())
                            <a href="{{ act('competition', 'vote', $comp->id) }}" class="btn btn-success">{{ $comp->canVote() ? 'Vote Now' : 'View Entries' }}</a>
                        @elseif ($comp->isJudging() && $comp->canJudge())
                            <a href="{{ act('competition-judging', 'view', $comp->id) }}" class="btn btn-secondary"><span class="fa fa-eye"></span> Go to Judging Panel</a>
                        @elseif ($comp->isClosed())
                            <div class="competition-winners">
                                <h5>Winners</h5>
                                @foreach ($comp->getEntriesForWinners() as $entry)
                                    {? $result = $comp->results->where('entry_id', $entry->id)->first(); ?}
                                    @avatar($entry->user small show_border=true show_name=false)
                                @endforeach
                            </div>
                        @else
                            {{ $comp->isActive() ? 'Closes' : 'Closed' }} @date($comp->getCloseTime()) ({{ $comp->getCloseTime()->format('jS F Y') }})
                        @endif
                    </div>
                    <div class="col-xl-8 col-lg-7 order-1 brief-column">
                        <div class="bbcode">{!! $comp->brief_html !!}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endsection
