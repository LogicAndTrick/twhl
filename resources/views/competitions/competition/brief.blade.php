@title('Competition: '.$comp->name)
@extends('app')

@section('content')
    <h1>
        Competition: {{ $comp->name }}

        @if (permission('CompetitionAdmin'))
            <a href="{{ act('competition-admin', 'delete', $comp->id) }}" class="btn btn-outline-danger btn-xs"><span class="fa fa-remove"></span> Delete</a>
            <a href="{{ act('competition-admin', 'edit-rules', $comp->id) }}" class="btn btn-outline-info btn-xs"><span class="fa fa-list"></span> Edit Rules</a>
            <a href="{{ act('competition-admin', 'edit', $comp->id) }}" class="btn btn-outline-primary btn-xs"><span class="fa fa-pencil"></span> Edit</a>
            @if ($comp->canJudge())
                <a href="{{ act('competition-judging', 'view', $comp->id) }}" class="btn btn-secondary btn-xs"><span class="fa fa-eye"></span> Manage Entries</a>
            @endif
        @endif
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
        <li class="active">View competition</li>
    </ol>

    {?
        $open_time = $comp->getOpenTime();
        $close_time = $comp->getCloseTime();
        $vote_open_time = $comp->getVotingOpenTime();
        $vote_close_time = $comp->getVotingCloseTime();
        $actual_closed = $vote_close_time == null ? $close_time : $vote_close_time;
        $days_since_close = $actual_closed->diffInDays();
        $collapse = $comp->isClosed() && $days_since_close <= 30;
    ?}

    @if ($collapse)
        <p class="text-center">
            <button id="collapse-button" class="btn btn-secondary" type="button" data-toggle="collapse" data-target="#brief-container">
                Show competition brief
                <span class="fa fa-chevron-down"></span>
            </button>
        </p>
    @endif

    <div class="collapse {{ $collapse ? 'hide' : 'show' }}" id="brief-container">
        <div class="slot">
            <div class="slot-main">
                <div class="row competition-brief">
                    <div class="col-xl-4 push-xl-8 col-lg-5 push-lg-7">

                        <div class="competition-status">
                            <span class="comp-status-message">Competition Status:</span>
                            <span class="comp-status">{{ $comp->getStatusText() }}</span>
                            @if ($comp->isOpen())
                                <div id="countdown" class="countdown"></div>
                            @elseif ($comp->isVotingOpen())
                                <a href="{{ act('competition', 'vote', $comp->id) }}" class="btn btn-success btn-lg">{{ $comp->canVote() ? 'Vote Now' : 'View Entries' }}</a>
                            @elseif ($comp->isJudging() && $comp->canJudge())
                                <a href="{{ act('competition-judging', 'view', $comp->id) }}" class="btn btn-secondary btn-lg"><span class="fa fa-eye"></span> Go to Judging Panel</a>
                                <hr/>
                                <a href="{{ act('competition-judging', 'preview', $comp->id) }}" class="btn btn-success btn-xs"><span class="fa fa-eye"></span> Preview Results</a>
                            @elseif ($comp->isClosed())
                                <a href="#results" class="btn btn-success btn-lg"><span class="fa fa-eye"></span> View Results</a>
                            @endif
                        </div>

                        <dl class="dl-horizontal dl-half">
                            <dt>Open Date</dt><dd>@date($open_time)</dd>
                            <dt>Close Date</dt><dd>@date($close_time)</dd>
                            @if ($comp->isVoted())
                            <dt>Voting Open Date</dt><dd>@date($vote_open_time)</dd>
                            <dt>Voting Close Date</dt><dd>@date($vote_close_time)</dd>
                            @endif
                            <dt>Type</dt><dd>{{ $comp->type->name }}</dd>
                            <dt>Allowed Engines</dt><dd>{{ implode(', ', $comp->engines->map(function($x) { return $x->name; })->toArray() ) }}</dd>
                            <dt>Judging Type</dt><dd>{{ $comp->judge_type->name }}</dd>
                            @if (count($comp->judges) > 0)
                            <dt>Judges</dt>
                            <dd>
                                <ul class="list-unstyled">
                                    @foreach ($comp->judges as $judge)
                                        <li>@avatar($judge inline)</li>
                                    @endforeach
                                </ul>
                            </dd>
                            @endif
                        </dl>

                        <hr class="hidden-lg-up" />

                    </div>
                    <div class="col-xl-8 pull-xl-4 col-lg-7 pull-lg-5 brief-column">
                        <div class="bbcode">{!! $comp->brief_html !!}</div>
                        @if ($comp->brief_attachment)
                            <div class="well well-sm">
                                Attached file:
                                <a href="{{ asset('uploads/competition/attachments/'.$comp->brief_attachment) }}" class="btn btn-success btn-xs">
                                    <span class="fa fa-download"></span>
                                    Click to download
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (($comp->isOpen() || $comp->isDraft()) && count($rule_groups) > 0)
        <hr />
        <h3>Competition Rules</h3>
        <ul class="competition-rules">
            @foreach ($rule_groups as $group => $rules)
                <li>
                    <strong>{{ $group }}</strong>
                    <ul>
                    @foreach ($rules as $rule)
                        <li><div class="bbcode">{!! $rule !!}</div></li>
                    @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>
    @endif
    @if ($user_entry)
        <h2>Your Entry</h2>
        @include('competitions.entry._entry', [ 'comp' => $comp, 'entry' => $user_entry ])
    @endif
    @if ($comp->isOpen() && permission('CompetitionEnter'))
        <h2>{{ $user_entry ? 'Update' : 'Submit' }} Entry</h2>
        @form(competition-entry/submit upload=true)
            @include('competitions.entry._entry-form-fields', [ 'comp' => $comp, 'entry' => $user_entry ])
        @endform
    @endif
    @if ($comp->isClosed())
        @include('competitions.competition._results', [ 'comp' => $comp ])
    @endif
@endsection

@section('scripts')
    <script type="text/javascript">
        $('#countdown').countdown({until: new Date({{ $comp->getCloseTime()->format('U') }} * 1000), description: 'Closes in:'});
        $('#brief-container').on('show.bs.collapse', function() {
            $('#collapse-button').parent().slideUp(function() {
                $('#collapse-button').parent().remove();
            });
        });
    </script>
    @include('competitions._gallery_javascript')
@endsection