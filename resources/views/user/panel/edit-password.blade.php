@extends('app')

@section('content')
    <h2>Update Password: {{ $user->name }}</h2>
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
