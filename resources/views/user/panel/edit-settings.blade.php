@title('Update site settings')
@extends('app')

@section('content')
    <hc>
        <h1>Update site settings: {{ $user->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
            <li class="active">Update Settings</li>
        </ol>
    </hc>
    @form(panel/edit-settings)
        @hidden(id $user)
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Email</h3>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-warning">
                            This email is what TWHL will use to communicate with you, including password resets.
                            Be very careful that it's valid! If it's not, your account may not be recoverable.
                        </div>
                        @text(email $user) = Email address
                        <div class="alert alert-info">
                            If you aren't afraid of spammers and want others to be able to contact you easily, you can show
                            your email address on your public profile.
                        </div>
                        @checkbox(show_email $user) = Show my email address on my public profile
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Time Zone</h3>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-info">
                            Dates and times on the site will use the time zone you select.
                            If you're not sure what time zone you live in, <a href="https://www.timeanddate.com/time/map/" target="_blank"><strong>use this map</strong></a> to find out.
                            TWHL doesn't support daylight savings time, so you should change your time zone manually if you want it to change.
                        </div>
                        @select(timezone $zones $user) = Time Zone
                    </div>
                </div>
            </div>
        </div>
        @submit = Update Settings
    @endform
@endsection
