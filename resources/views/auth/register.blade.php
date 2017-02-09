@title('Register Account')
@extends('app')

@section('content')

    <hc>
        <h1>Register a TWHL account</h1>
    </hc>
    <div class="row">
        <div class="col-xl-4 push-xl-4 col-md-6 push-md-3">
            @form(auth/register)
                @text(name) = Username

                <hr/>
                <p class="text-info">
                    Your email address is the only way we can contact you if you forget your password.
                    Please make sure it's correct!
                </p>
                @text(email) = Email
                @text(email_confirmation) = Confirm Email
                <hr/>

                @password(password) = Password
                @password(password_confirmation) = Confirm Password
                <hr/>

                {!! Recaptcha::render() !!}

                <hr/>
                <p>
                    TWHL has some simple rules that we expect our users to follow.
                    <a href="{{ url('/wiki/page/TWHL:_Site_Rules') }}">You can read them here,</a>
                    but the basic summary is:
                </p>
                <ol>
                    <li>Be nice.</li>
                    <li>No piracy.</li>
                    <li>No spam.</li>
                </ol>
                @checkbox(agree_rules) = I agree to follow the rules of the site
                <hr/>

                <div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            @endform
        </div>
    </div>
@endsection
