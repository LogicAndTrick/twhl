@title('Reset Password')
@extends('app')

@section('content')
    <hc>
        <h1>Reset password</h1>
    </hc>
    <div class="row">
        <div class="col-md-4 col-md-push-4 col-sm-6 col-sm-push-3">
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
