@title('Request Password Reset')
@extends('app')

@section('content')
    <h1>
        <span class="fa fa-life-ring"></span>
        Request password reset
    </h1>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="row">
        <div id="password-reset-form" class="col-xl-4 offset-xl-4 col-md-6 offset-md-3">
            @form(password/email)
                @text(email) = Email
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Send Password Reset Link</button>
                </div>
            @endform
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        const form = document.getElementById('password-reset-form').querySelector('form');
        form.addEventListener('submit', () => {
            form.querySelector('button').disabled = true;
        });
    </script>
@endsection