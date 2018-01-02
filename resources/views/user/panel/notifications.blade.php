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
        <span class="fa fa-bell"></span> Notifications
        <a href="{{ act('panel', 'clear-notifications') }}" class="btn btn-xs btn-outline-primary"><span class="fa fa-check"></span> Mark all as read</a>
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
                            <span class="badge badge-success">{{ $notify->stat_hits }}</span>
                        @endif
                        {{ $notify->type_description ? $notify->type_description : '[No title]' }}
                    </td>
                    <td><a href="{{ $notify->link }}">{{ $notify->title }}</a></td>
                    <td class="col-30p">@date($notify->created_at)</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2><span class="fa fa-bell-o"></span> Subscriptions</h2>
    <table class="table">
        <thead>
            <tr>
                <th class="col-30p">Type</th>
                <th>Article</th>
                <!--th class="col-15p">Send Email?</th-->
                <th class="col-15p"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($subscriptions as $subscription)
                <tr>
                    <td class="col-30p">{{ $subscription->type_description }}</td>
                    <td><a href="{{ $subscription->link }}">{{ $subscription->title ? $subscription->title : '[No title]' }}</a></td>
                    <!--td class="col-15p">{{ $subscription->send_email ? 'Yes' : 'No' }}</td-->
                    <td class="text-right col-15p">
                        <a href="{{ act('panel', 'delete-subscription', $subscription->id) }}" class="btn btn-xs btn-outline-danger"><span class="fa fa-remove"></span> Unsubscribe</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection