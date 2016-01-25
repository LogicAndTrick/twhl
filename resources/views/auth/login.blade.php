@title('Login')
@extends('app')

@section('content')
    <hc>
        <h1>Login to TWHL</h1>
    </hc>
    <div class="row">
        <div class="col-md-4 col-md-push-4 col-sm-6 col-sm-push-3">
            @form(auth/login)
                @text(email) = Email or Username
                @password(password) = Password
                @checkbox(remember) = Remember Me
                <div>
                    <button type="submit" class="btn btn-primary">Login</button>
                    <a class="btn btn-link" href="{{ url('/password/email') }}">Forgot Your Password?</a>
                </div>
            @endform
        </div>
    </div>
@endsection
