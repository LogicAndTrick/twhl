@title('Reset Password')
@extends('app')

@section('content')
    <h1>
        <span class="fa fa-life-ring"></span>
        Reset password
    </h1>
    <div class="row">
        <div class="col-xl-4 offset-xl-4 col-md-6 offset-md-3">
            @form(password/reset)
                @hidden(token $token)
                @text(email) = Email
                @password(password) = New Password
                @password(password_confirmation) = Confirm Password

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            @endform
        </div>
    </div>
@endsection
