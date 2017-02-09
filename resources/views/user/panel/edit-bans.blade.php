@title('Manage bans: '.$user->name)
@extends('app')

@section('content')
    <hc>
        <h1>Manage bans: {{ $user->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
            <li class="active">Manage Bans</li>
        </ol>
    </hc>

    <h2>Ban History</h2>
    <table class="table">
        <tr>
            <th>Status</th>
            <th>Reason</th>
            <th>IP</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th></th>
        </tr>
        @foreach ($bans as $ban)
            <tr>
                <td>{{ $ban->isActive() ? 'Active Ban' : 'Expired Ban' }}</td>
                <td>{{ $ban->reason }}</td>
                <td>{{ $ban->ip }}</td>
                <td>{{ $ban->created_at->format('Y-m-d H:i:s') }}Z ({{ $ban->created_at->diffForHumans() }})</td>
                <td>{{ !$ban->ends_at ? 'Never' : $ban->ends_at->format('Y-m-d H:i:s') . 'Z (' . $ban->ends_at->diffForHumans() . ')' }}</td>
                <td>
                    @form(panel/delete-ban)
                        @hidden(id $ban)
                        <button class="btn btn-danger btn-xs" type="submit">
                            <span class="fa fa-remove"></span>
                            Delete
                        </button>
                    @endform
                </td>
            </tr>
        @endforeach
    </table>

    <div class="alert alert-danger">
        <strong>Careful!</strong>
        This is an administration action.
        Banning people shouldn't be done without good reason. Make sure the reason
        clearly explains why the user was banned.
    </div>

    <h2>Ban This User</h2>
    @form(panel/add-ban)
        @hidden(id $user)
        @text(reason) = Reason
        @text(duration) = Number of units
        <div class="form-group">
            <label for="unit">Unit</label>
            <select class="form-control" id="unit" name="unit">
                <option value="1">Hour</option>
                <option value="24" selected>Day</option>
                <option value="{{ 24 * 7 }}">Week</option>
                <option value="{{ 24 * 30 }}">Month</option>
                <option value="{{ 24 * 365 }}">Year</option>
                <option value="-1">Forever</option>
            </select>
        </div>
        @checkbox(ip_ban) = Also ban by IP address (prevent anonymous log in)
        @submit = Ban User
    @endform
@endsection
