@extends('app')

@section('content')

    <hc>
        <h1>Register a TWHL Account</h1>
    </hc>
    <div class="row">
        <div class="col-md-4 col-md-push-4 col-sm-6 col-sm-push-3">
            @form(auth/register)
                @text(name) = Username
                @text(email) = Email
                @password(password) = Password
                @password(password_confirmation) = Confirm Password
                <div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            @endform
        </div>
    </div>
@endsection
