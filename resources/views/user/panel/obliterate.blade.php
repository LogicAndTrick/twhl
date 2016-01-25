@title('Obliterate User: '.$user->name)
@extends('app')

@section('content')
    <hc>
        <h1>Obliterate User: @avatar($user inline)</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
            <li class="active">Obliterate</li>
        </ol>
    </hc>

    <div class="alert alert-danger">
        <h2 style="font-size: 2em;">Obliteration cannot be reversed!</h2>
        <p>
            Obliterating users should be reserved exclusively for spammers!
            It will delete all content that the user has ever posted,
            and will permanently ban the user's IP address.
            This is an extreme option and should not be taken lightly.
        </p>
        <p>
            This is an administration action.
        </p>
    </div>

    <h2>Please confirm the obliteration of @avatar($user inline)</h2>
    @form(panel/obliterate)
        @hidden(id $user)
        @checkbox(sure) = I want to obliterate this user, which will delete all their data and ban them.
        @checkbox(sure_confirmation) = Double check: I'm definitely sure that I want to obliterate this user!
        @submit = Obliterate User - THIS ACTION CANNOT BE REVERSED!
    @endform
@endsection
