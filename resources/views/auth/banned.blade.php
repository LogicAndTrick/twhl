@title('Banned')
@extends('app')

@section('content')
    <h1>
        <span class="fa fa-ban"></span>
        You have been banned
    </h1>
    <p>
        You have been banned from TWHL.
        @if ($ban->reason)
            <br/>
            Reason: <strong>{{ $ban->reason }}</strong>
        @endif
        @if ($ban->ends_at)
            <br/>
            You will remain banned until: <strong>{{ $ban->ends_at->format('Y-m-d H:i:s') }} UTC ({{ $ban->ends_at->diffForHumans() }})</strong>
        @endif
    </p>
    <div class="text-center">
        <a href="{{ act('auth', 'logout') . '?_token=' . csrf_token() }}" class="btn btn-lg btn-primary">
            Log Out
        </a>
    </div>
@endsection
