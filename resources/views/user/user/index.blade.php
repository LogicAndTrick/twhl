@extends('app')

@section('content')
    <hc>
        <h1>TWHL Members</h1>
        {!! $users->render() !!}
    </hc>
    <div class="row">
        @foreach ($users as $user)
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="media media-panel media-panel-small">
                    <div class="media-left">
                        <div class="media-object">
                            @avatar($user small show_name=false)
                        </div>
                    </div>
                    <div class="media-body">
                        @avatar($user text)<br/>
                        Joined: @date($user->created_at)
                        @if (permission('Admin'))
                            <br/>
                            <em>{{ $user->last_access_ip }}</em>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="footer-container">
        {!! $users->render() !!}
    </div>
@endsection