@title('Request Password Reset')
@extends('app')

@section('content')
    <hc>
        <h1>Request Password Reset</h1>
    </hc>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-4 col-md-push-4 col-sm-6 col-sm-push-3">
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