@title('Competition: '.$comp->name)
@extends('app')

@section('content')
    <hc>
        @if (permission('CompetitionAdmin'))
            <a href="{{ act('competition-admin', 'delete', $comp->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Delete</a>
            <a href="{{ act('competition-admin', 'edit-rules', $comp->id) }}" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-list-alt"></span> Edit Rules</a>
            <a href="{{ act('competition-admin', 'edit', $comp->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
            @if ($comp->canJudge())
                <a href="{{ act('competition-judging', 'view', $comp->id) }}" class="btn btn-inverse btn-xs"><span class="glyphicon glyphicon-eye-open"></span> Manage Entries</a>
            @endif
        @endif
        <h1>Competition: {{ $comp->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li class="active">View competition</li>
        </ol>
    </hc>

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
            <button id="collapse-button" class="btn btn-default" type="button" data-toggle="collapse" data-target="#brief-container">
                Show competition brief
                <span class="glyphicon glyphicon-chevron-down"></span>
            </button>
        </p>
    @endif

    <div class="collapse {{ $collapse ? '' : 'in' }}" id="brief-container">
        <div class="row competition-brief">
            <div class="col-lg-4 col-lg-push-8 col-md-5 col-md-push-7">

                <div class="competition-status">
                    <span class="comp-status-message">Competition Status:</span>
                    <span class="comp-status">{{ $comp->getStatusText() }}</span>
                    @if ($comp->isOpen())
                        <div id="countdown" class="countdown"></div>
                    @elseif ($comp->isVotingOpen())
                        <a href="{{ act('competition', 'vote', $comp->id) }}" class="btn btn-success btn-lg">{{ $comp->canVote() ? 'Vote Now' : 'View Entries' }}</a>
                    @elseif ($comp->isJudging() && $comp->canJudge())
                        <a href="{{ act('competition-judging', 'view', $comp->id) }}" class="btn btn-inverse btn-lg"><span class="glyphicon glyphicon-eye-open"></span> Go to Judging Panel</a>
                        <hr/>
                        <a href="{{ act('competition-judging', 'preview', $comp->id) }}" class="btn btn-success btn-xs"><span class="glyphicon glyphicon-eye-open"></span> Preview Results</a>
                    @elseif ($comp->isClosed())
                        <a href="#results" class="btn btn-success btn-lg"><span class="glyphicon glyphicon-eye-open"></span> View Results</a>
                    @endif
                </div>

                <dl class="dl-horizontal">
                    <dt>Open Date</dt><dd>@date($open_time)</dd>
                    <dt>Close Date</dt><dd>@date($close_time)</dd>
                    @if ($comp->isVoted())
                    <dt>Voting Open Date</dt><dd>@date($vote_open_time)</dd>
                    <dt>Voting Close Date</dt><dd>@date($vote_close_time)</dd>
                    @endif
                    <dt>Type</dt><dd>{{ $comp->type->name }}</dd>
                    <dt>Judging Type</dt><dd>{{ $comp->judge_type->name }}</dd>
                    <dt>Allowed Engines</dt><dd>{{ implode(', ', $comp->engines->map(function($x) { return $x->name; })->toArray() ) }}</dd>
                    @if (count($comp->judges) > 0)
                    <dt>Judges</dt>
                    <dd>
                        {? $i = 0 ?}
                        @foreach ($comp->judges as $judge)
                            {!! $i++ != 0 ? '&bull;' : '' !!}
                            @avatar($judge inline)
                        @endforeach
                    </dd>
                    @endif
                </dl>

                <hr class="visible-xs-block visible-sm-block" />

            </div>
            <div class="col-lg-8 col-lg-pull-4 col-md-7 col-md-pull-5 brief-column">
                <div class="bbcode">{!! $comp->brief_html !!}</div>
                @if ($comp->brief_attachment)
                    <div class="well well-sm">
                        Attached file:
                        <a href="{{ asset('uploads/competition/attachments/'.$comp->brief_attachment) }}" class="btn btn-success btn-xs">
                            <span class="glyphicon glyphicon-download-alt"></span>
                            Click to download
                        </a>
                    </div>
                @endif
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
        <hr>
        <h3>Your Entry</h3>
        @include('competitions.entry._entry', [ 'comp' => $comp, 'entry' => $user_entry ])
        @include('competitions._gallery_javascript')
    @endif
    @if ($comp->isOpen() && permission('CompetitionEnter'))
        <hr>
        <h3>{{ $user_entry ? 'Update' : 'Submit' }} Entry</h3>
        @form(competition-entry/submit upload=true)
            @include('competitions.entry._entry-form-fields', [ 'comp' => $comp, 'entry' => $user_entry ])
        @endform
    @endif
    @if ($comp->isClosed())

        <a id="results"></a>
        <hc>
            <h1>
                Results
                @if ($comp->isJudged() && $comp->judges->count() > 0)
                    <small class="pull-right">
                        Judged By:
                        {? $i = 0; ?}
                        @foreach ($comp->judges as $judge)
                            {!! $i++ == 0 ? '' : ' &bull; ' !!}
                            @avatar($judge inline)
                        @endforeach
                    </small>
                @endif
            </h1>
        </hc>

        <div class="competition-results">
            @if ($comp->results_intro_html)
                <div class="bbcode">{!! $comp->results_intro_html !!}</div>
            @endif
            <ul class="media-list">
                {? $prev_rank = -1; ?}
                @foreach ($comp->getEntriesForResults() as $entry)
                    {? $result = $comp->results->where('entry_id', $entry->id)->first(); ?}
                    @if ($prev_rank != 0 && $result->rank == 0)
                        </ul>
                        <hr/>
                        <h3>Other Entries</h3>
                        <ul class="media-list">
                    @endif
                    {? $shot = $entry->screenshots->first(); ?}
                    {? $prev_rank = $result->rank; ?}
                    {? $result = $comp->results->where('entry_id', $entry->id)->first(); ?}
                    <li class="media media-panel" data-id="{{ $entry->id }}">
                        <div class="ribbon {{ $result->rank > 0 ? 'info' : '' }}">
                            <div class="right">
                                @if ($entry->file_location)
                                    <a href="{{ $entry->getLinkUrl() }}" class="btn btn-success btn-xs"><span class="glyphicon glyphicon-download-alt"></span> Download</a>
                                @endif
                            </div>
                            <div class="left">
                                @avatar($entry->user small show_name=false)
                            </div>
                            @if ($result->rank == 1)
                                <h2>1st Place - @avatar($entry->user text)</h2>
                            @elseif ($result->rank == 2)
                                <h2>2nd Place - @avatar($entry->user text)</h2>
                            @elseif ($result->rank == 3)
                                <h2>3rd Place - @avatar($entry->user text)</h2>
                            @endif
                            <h3>{{ $entry->title }}</h3>
                        </div>
                        <div class="media-body">
                            <div class="visible-sm-block visible-xs-block text-center">
                                <div style="display: inline-block;">
                                    <a href="#" class="gallery-button img-thumbnail">
                                        @if ($shot)
                                            <img class="media-object" src="{{ asset('uploads/competition/'.$shot->image_thumb) }}" alt="Screenshot" />
                                        @else
                                            <img class="media-object" src="{{ asset('images/no-screenshot-320.png') }}" alt="Screenshot" />
                                        @endif
                                    </a>
                                    @if ($entry->screenshots->count() > 1)
                                        <button class="btn btn-info btn-block gallery-button" type="button">
                                            <span class="glyphicon glyphicon-picture"></span>
                                            + {{ $entry->screenshots->count()-1 }} more screenshot{{ $entry->screenshots->count() == 2 ? '' : 's' }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="bbcode">{!! $result->content_html !!}</div>
                        </div>
                        <div class="media-right hidden-xs hidden-sm">
                            <a href="#" class="gallery-button img-thumbnail">
                                @if ($shot)
                                    <img class="media-object" src="{{ asset('uploads/competition/'.$shot->image_thumb) }}" alt="Screenshot" />
                                @else
                                    <img class="media-object" src="{{ asset('images/no-screenshot-320.png') }}" alt="Screenshot" />
                                @endif
                            </a>
                            @if ($entry->screenshots->count() > 1)
                                <button class="btn btn-info btn-block gallery-button" type="button">
                                    <span class="glyphicon glyphicon-picture"></span>
                                    + {{ $entry->screenshots->count()-1 }} more screenshot{{ $entry->screenshots->count() == 2 ? '' : 's' }}
                                </button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
            @if ($comp->results_outro_html)
                <div class="bbcode">{!! $comp->results_outro_html !!}</div>
            @endif
        </div>

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