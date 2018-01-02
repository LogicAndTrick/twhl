@title('User control panel: '.$user->name)
@extends('app')

@section('content')
    <h1>
        <span class="fa fa-cogs"></span>
        User control panel: {{ $user->name }}
    </h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Links
                </div>
                <div class="card-block">
                    <ul class="list-unstyled">
                        <li><a href="{{ act('user', 'view', $user->id) }}"><span class="fa fa-user"></span> View Public Profile</a></li>
                        <li><a href="{{ act('vault', 'index').'?users='.$user->id }}"><span class="fa fa-files-o"></span> View Vault Items</a></li>
                        <li><a href="{{ act('journal', 'index').'?user='.$user->id }}"><span class="fa fa-book"></span> View Journals</a></li>
                        <li><a href="{{ act('thread', 'index').'?user='.$user->id }}"><span class="fa fa-list-alt"></span> View Forum Threads</a></li>
                        <li><a href="{{ act('post', 'index').'?user='.$user->id }}"><span class="fa fa-list"></span> View Forum Posts</a></li>
                        <li><a href="{{ url('message/index/'.$user->id) }}"><span class="fa fa-envelope"></span> View Private Messages</a></li>
                        <li><a href="{{ url('panel/notifications/'.$user->id) }}"><span class="fa fa-bell"></span> Notifications and Subscriptions</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 mt-3 mt-md-0">
            <div class="card">
                <div class="card-header">
                    Actions
                </div>
                <div class="card-block">
                    <ul class="list-unstyled">
                        <li><a href="{{ act('panel', 'edit-profile', $user->id) }}"><span class="fa fa-pencil"></span> Edit Public Profile</a></li>
                        <li><a href="{{ act('panel', 'edit-avatar', $user->id) }}"><span class="fa fa-picture-o"></span> Change Avatar</a></li>
                        <li><a href="{{ act('panel', 'edit-password', $user->id) }}"><span class="fa fa-lock"></span> Update Password</a></li>
                        <li><a href="{{ act('panel', 'edit-settings', $user->id) }}"><span class="fa fa-cog"></span> Edit Site Settings</a></li>
                        <li><a href="{{ act('panel', 'edit-keys', $user->id) }}"><span class="fa fa-certificate"></span> Manage Api Keys</a></li>
                    </ul>
                    @if (permission('Admin'))
                        <hr title="Admin Actions"/>
                        <ul class="list-unstyled">
                            <li><a href="{{ act('panel', 'edit-name', $user->id) }}"><span class="fa fa-user"></span> Change User's Name</a></li>
                            <li><a href="{{ act('panel', 'edit-bans', $user->id) }}"><span class="fa fa-ban"></span> Manage User's Bans</a></li>
                            @if (permission('ObliterateAdmin') && $user->id != Auth::user()->id)
                                <li><a class="text-danger" href="{{ act('panel', 'obliterate', $user->id) }}"><span class="fa fa-trash"></span> Obliterate User</a></li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-header">
            Profile
        </div>
        <div class="card-block">
            @include('user._profile', [ 'user' => $user ])
        </div>
    </div>
@endsection
