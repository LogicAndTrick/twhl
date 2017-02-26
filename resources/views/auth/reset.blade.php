@title('Reset Password')
@extends('app')

@section('content')
    <h1>Reset password</h1>
    <div class="row">
        <div class="col-xl-4 push-xl-4 col-md-6 push-md-3">
            @form(password/reset)
                @hidden(token $token)
                @text(email) = Email
                @password(password) = New Password
                @password(password_confirmation) = Confirm Password

                <div>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            @endform
        </div>
    </div>
@endsection
