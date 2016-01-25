@title('Competition Result Voting: '.$comp->name)
@extends('app')

@section('content')
    <hc>
        @if ($comp->canJudge())
            <a href="{{ act('competition-judging', 'view', $comp->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span> View/Edit Results</a>
        @endif
        <h1>Competition Voting: {{ $comp->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
            <li class="active">Voting</li>
        </ol>
    </hc>
    <div class="alert alert-info">
        @if ($comp->isVotingOpen())
            <h3>Voting ends {{ $comp->getVotingCloseTime()->format('jS F, Y') }} at {{ $comp->getVotingCloseTime()->format('H:i') }} GMT ({{ $comp->getVotingCloseTime()->diffForHumans() }})</h3>
            <ol>
                <li>The order of the entries is randomised each time the page is refreshed</li>
                <li>You can vote for up to 3 entries - each vote counts as one point</li>
                <li>In the case of a tie, the TWHL admins and moderators will decide on which entry wins the tie-breaker</li>
                <li>You can't vote if you entered the competition</li>
                <li>You can't vote if your account was created after the competition started</li>
                <li>Please don't vote with more than one account if you have multiple accounts</li>
            </ol>
        @else
            <p>Voting has ended for this competition</p>
        @endif
    </div>
    @if (!$comp->canVote())
        <div class="alert alert-warning">
            <h3>Sorry, you are not eligable to vote for this competition.</h3>
            <p>Reason: {{ $comp->cantVoteReason() }}</p>
        </div>
    @endif
    <div class="row">
        @foreach ($comp->entries->shuffle() as $entry)
            <div data-id="{{ $entry->id }}" class="col-md-4 thumbnail competition-vote-entry {{ $votes->contains($entry->id) ? 'voted' : '' }}">
                <h3>{{ $entry->title }}</h3>
                <h4>By @avatar($entry->user inline)</h4>
                {? $shot = $entry->screenshots->first(); ?}
                <a href="#" class="gallery-button">
                @if ($shot)
                    <img src="{{ asset('uploads/competition/'.$shot->image_thumb) }}" alt="Screenshot" />
                @else
                    <img src="{{ asset('images/no-screenshot-320.png') }}" alt="Screenshot" />
                @endif
                </a>
                @if ($entry->screenshots->count() > 1)
                    <button class="btn btn-info btn-block gallery-button" type="button">
                        <span class="glyphicon glyphicon-picture"></span>
                        + {{ $entry->screenshots->count()-1 }} more screenshot{{ $entry->screenshots->count() == 2 ? '' : 's' }}
                    </button>
                @endif
                @if ($comp->canVote())
                <button class="btn btn-success btn-block btn-sm vote-button {{ $votes->contains($entry->id) ? 'active' : '' }}" type="button">
                    <span class="glyphicon glyphicon-ok"></span>
                    <span class="vote-status">{{ $votes->contains($entry->id) ? 'You voted for this entry!' : 'Vote for this entry' }}</span>
                </button>
                @endif
            </div>
        @endforeach
    </div>
@endsection

@section('scripts')
    @include('competitions._gallery_javascript')
    <script type="text/javascript">
        var vote_url_template = "{{ url('competition/add-vote/{id}') }}";
        $(function() {
            $('.vote-button').click(function() {
                var $t = $(this),
                    par = $t.closest('[data-id]').addClass('loading'),
                    id = par.data('id'),
                    obj = ({id});
                $.get(template(vote_url_template, obj)).done(function(result) {
                    $t.find('.vote-status').text(result.status);
                    $t.toggleClass('active', result.is_voted_for);
                    par.toggleClass('voted', result.is_voted_for).removeClass('loading');
                });
            });
        });
    </script>
@endsection