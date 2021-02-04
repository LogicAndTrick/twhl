@title('Welcome Back!')
@extends('app')

@section('content')

    <h1>
        <span class="fa fa-smile-o"></span>
        Welcome to the new and exciting TWHL!
    </h1>

    <div class="alert alert-info">
        <p>
            Welcome back to TWHL! Please take a moment to confirm your details.
            <strong>For security reasons, your password must be reset to use the site,</strong>
            but you can use the same password if you want to.
        </p>
    </div>
    <div class="row">
        <div class="col-xl-4 offset-xl-4 col-md-6 offset-md-3">
            @form(auth/convert)

                <p class="text-info">
                    Your email address is the only way we can contact you if you forget your password.
                    Please make sure it's correct!
                </p>
                @text(email $user) = Email
                <hr/>


                <p class="text-info">
                    Your password must be reset to use TWHL's new encryption method.
                    You can reset to the same password if you want to.
                </p>
                @password(old_password) = Current Password
                @password(password) = New Password
                @password(password_confirmation) = Confirm New Password
                <hr/>

                <p>
                    TWHL has some simple rules that we expect our users to follow.
                    <a href="{{ url('/wiki/page/TWHL:_Site_Rules') }}">You can read them here,</a>
                    but the basic summary is:
                </p>
                <ol>
                    <li>Be respectful and inclusive.</li>
                    <li>Be nice.</li>
                    <li>No piracy.</li>
                    <li>No spam.</li>
                </ol>
                @checkbox(agree_rules) = I agree to follow the rules of the site
                <hr/>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Continue</button>
                </div>
            @endform
        </div>
    </div>
@endsection
