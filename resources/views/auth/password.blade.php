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
        <div class="col-xl-4 push-xl-4 col-md-6 push-md-3">
            @form(password/email)
                @text(email) = Email
                <div>
                    <button type="submit" class="btn btn-primary">Send Password Reset Link</button>
                </div>
            @endform
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $('form').submit(function() {
            $(this).find('button').prop('disabled', true);
        });
    </script>
@endsection