@extends('app')

@section('content')
    <h2>User Control Panel: {{ $user->name }}</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Links</h3>
                </div>
                <div class="panel-body">
                    <ul>
                        <li><a href="{{ act('user', 'view', $user->id) }}"><span class="glyphicon glyphicon-user"></span> View Public Profile</a></li>
                        <li><a href="{{ act('user', 'view', $user->id) }}"><span class="glyphicon glyphicon-file"></span> View Vault Items</a></li>
                        <li><a href="{{ act('user', 'view', $user->id) }}"><span class="glyphicon glyphicon-book"></span> View Journals</a></li>
                        <li><a href="{{ act('user', 'view', $user->id) }}"><span class="glyphicon glyphicon-th-list"></span> View Forum Threads</a></li>
                        <li><a href="{{ act('user', 'view', $user->id) }}"><span class="glyphicon glyphicon-list-alt"></span> View Forum Posts</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Actions</h3>
                </div>
                <div class="panel-body">
                    <ul>
                        <li><a href="{{ act('panel', 'edit-profile', $user->id) }}"><span class="glyphicon glyphicon-pencil"></span> Edit Public Profile</a></li>
                        <li><a href="{{ act('panel', 'edit-avatar', $user->id) }}"><span class="glyphicon glyphicon-picture"></span> Change Avatar</a></li>
                        <li><a href="{{ act('panel', 'edit-password', $user->id) }}"><span class="glyphicon glyphicon-lock"></span> Update Password</a></li>
                        <li><a href="{{ act('panel', 'edit-settings', $user->id) }}"><span class="glyphicon glyphicon-cog"></span> Edit Site Settings</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
