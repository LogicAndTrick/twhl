@title('Notifications and subscriptions')
@extends('app')

@section('content')
    <h1>Notifications and subscriptions: {{ $user->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
        <li class="active">Notifications and subscriptions</li>
    </ol>

    <div class="alert alert-info">You are automatically subscribed to your own articles.</div>

    <h2>
        <span class="fas fa-bell"></span> Notifications
        @form(panel/clear-notifications)
            @hidden(id $user)
            <button type="submit" class="btn btn-xs btn-outline-primary"><span class="fas fa-check"></span> Mark all as read</button>
        @endform
    </h2>
    <table class="table">
        <thead>
            <tr>
                <th class="col-30p">Type</th>
                <th>Article</th>
                <th class="col-30p">Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notifications as $notify)
                <tr class="{{ $notify->is_unread ? 'unread' : '' }}">
                    <td class="col-30p">
                        @if ($notify->is_unread)
                            <span class="badge bg-success">{{ $notify->stat_hits }}</span>
                        @endif
                        {{ $notify->type_description }}
                    </td>
                    <td><a href="{{ $notify->link }}">{{ $notify->title && strlen($notify->title) > 0 ? $notify->title : '[No title]' }}</a></td>
                    <td class="col-30p">@date($notify->created_at)</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2><span class="far fa-bell"></span> Subscriptions</h2>
    <table class="table">
        <thead>
            <tr>
                <th class="col-30p">Type</th>
                <th>Article</th>
                <th class="col-15p"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($subscriptions as $subscription)
                <tr>
                    <td class="col-30p">{{ $subscription->type_description }}</td>
                    <td><a href="{{ $subscription->link }}">{{ $subscription->title && strlen($subscription->title) > 0 ? $subscription->title : '[No title]' }}</a></td>
                    <td class="text-end col-15p">
                        @form(panel/delete-subscription)
                            @hidden(id $subscription)
                            <button type="submit" class="btn btn-xs btn-outline-danger"><span class="fas fa-xmark"></span> Unsubscribe</button>
                        @endform
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection