@title('Register Account')
@extends('app')

@section('scripts')
    {!! ReCaptcha::htmlScriptTagJsApi() !!}
@endsection

@section('content')

    <h1>
        <span class="fa fa-user-plus"></span>
        Register a TWHL account
    </h1>

    <div class="row">
        <div class="col-xl-4 offset-xl-4 col-md-6 offset-md-3">
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

                <div class="d-flex justify-content-center">
                    {!! ReCaptcha::htmlFormSnippet() !!}
                </div>
                @error('g-recaptcha-response')
                    <p class="help-block text-danger">Please check the box to prove that you're not a robot.</p>
                @enderror
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
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            @endform
        </div>
    </div>
@endsection
