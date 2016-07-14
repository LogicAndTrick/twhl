@title('Notifications and subscriptions')
@extends('app')

@section('content')
    <hc>
        <h1>Notifications and subscriptions: {{ $user->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
            <li class="active">Notifications and subscriptions</li>
        </ol>
    </hc>

    <h2>Notifications</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Title</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notifications as $notify)
                <tr>
                    <td>{{ $notify->type_description }}</td>
                    <td><a href="{{ $notify->link }}">{{ $notify->title }}</a></td>
                    <td>@date($notify->created_at)</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection