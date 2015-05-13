@extends('app')

@section('content')
    <h2>
        Competition Voting: {{ $comp->name }}
    </h2>
    <div class="alert alert-info">
        <h3>Voting Ends {{ $comp->getVotingCloseTime()->format('jS F, Y') }} at {{ $comp->getVotingCloseTime()->format('H:i') }} GMT ({{ $comp->getVotingCloseTime()->diffForHumans() }})</h3>
        <ol>
            <li>The order of the entries is randomised each time the page is refreshed</li>
            <li>You can't vote if you entered the competition</li>
            <li>You can't vote if your account was created after the competition started</li>
            <li>Please don't vote with more than one account if you have multiple accounts</li>
        </ol>
    </div>
    <div class="row">
        @foreach ($comp->entries->shuffle() as $entry)
            <div class="col-md-3">
                {{ $entry }}
            </div>
        @endforeach
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        //
    </script>
@endsection