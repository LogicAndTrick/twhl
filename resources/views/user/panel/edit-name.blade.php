@title('Update Username: '.$user->name)
@extends('app')

@section('content')
    <hc>
        <h1>Update Username: {{ $user->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
            <li class="active">Update Name</li>
        </ol>
    </hc>

    <div class="alert alert-danger">
        <strong>Careful!</strong>
        This is an administration action. Try not to change people's names
        too often. It's annoying.
    </div>

    @form(panel/edit-name)
        @hidden(id $user)
        <div class="form-group">
            <strong>Current Username</strong><br>
            {{ $user->name }}
        </div>
        @text(new_name $user->name) = New Username
        @submit = Update Username
    @endform
@endsection
