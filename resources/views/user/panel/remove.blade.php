@title('Delete user: '.$user->name)
@extends('app')

@section('content')
    <h1>Delete user: @avatar($user inline)</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
        <li class="active">Delete</li>
    </ol>

    <div class="alert alert-danger">
        <h2 style="font-size: 2em;">This action cannot be reversed!</h2>
        <p>
            Deleting an account should only be done when the owner of the account
            has requested it. Before continuing with this, you should check
            the user's content to make sure nothing key to the site is lost.
        </p>
        <p>
            This action is different to obliteration - it will not ban the user
            or their IP address. They are free to create a new account. Some content
            from this user will remain, such as wiki edits. The account will not be
            deleted entirely, but will be anonymised and the majority of data will be
            deleted.
        </p>
        <p>
            This is an administration action.
        </p>
    </div>

    <h2>Please confirm the deletion of @avatar($user inline)</h2>
    @form(panel/remove)
        @hidden(id $user)
        @checkbox(sure) = I want to delete this user, which will delete all their data.
        @checkbox(sure_confirmation) = Double check: I'm definitely sure that I want to delete this user!
        @submit = Delete User - THIS ACTION CANNOT BE REVERSED!
    @endform
@endsection
