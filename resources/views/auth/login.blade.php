@title('Login')
@extends('app')

@section('content')
    <h1>Login to TWHL</h1>

    <div class="row">
        <div class="col-xl-4 push-xl-4 col-md-6 push-md-3">
            @form(auth/login)
                {? $login_form_checked = true; ?}
                @text(email) = Email or Username
                @password(password) = Password
                @checkbox(remember $login_form_checked) = Remember Me
                <div>
                    <button type="submit" class="btn btn-primary">Login</button>
                    <a class="btn btn-link" href="{{ url('/password/email') }}">Forgot Your Password?</a>
                </div>
            @endform
        </div>
    </div>
@endsection
