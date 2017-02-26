@title('Update password: '.$user->name)
@extends('app')

@section('content')
    <h1>Update password: {{ $user->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
        <li class="active">Update Password</li>
    </ol>

    @form(panel/edit-password)
        @hidden(id $user)
        @if ($need_original)
            @password(original_password) = Current Password
        @endif
        @password(password) = New Password
        @password(password_confirmation) = Confirm New Password
        @submit = Update Password
    @endform
@endsection
