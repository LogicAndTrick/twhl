@title('Come Back Later')
@extends('app')

@section('content')
    <h1>
        <span class="fa fa-ban"></span>
        Account creation is rate-limited
    </h1>
    <p>
        Due to excessive spam accounts being created, user account creation has been rate-limited to 1 per hour.
    </p>
    <ul>
        <li>If you are not a spammer, please come back in 1 hour to try again.</li>
        <li>If you are a spammer, please stop.</li>
    </ul>
@endsection
