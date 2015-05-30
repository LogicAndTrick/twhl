@extends('app')

@section('content')
    <h2>Update Avatar: {{ $user->name }}</h2>
    <div class="alert alert-info">You can choose to upload your own avatar or alternatively, you can use one of our presets.</div>

    <div class="text-center">
        <p>Current avatar:</p>
        <img class="img-thumbnail" src="{{ $user->avatar_full }}" alt="Avatar">
    </div>

    <h3>Upload Custom Avatar</h3>
    <div class="alert alert-default">
        <ul>
            <li>The maximum avatar size is 100 x 100, your image will be resized if it is too large.</li>
            <li>The recommended format is PNG, with an alpha channel if required.</li>
            <li>Inappropriate content will be deleted and may lead to your account being banned.</li>
        </ul>
    </div>
    @form(panel/edit-avatar upload=true)
        @hidden(id $user)
        <input type="hidden" name="type" value="upload" />
        @file(upload) = Choose Image (png or jpg)
        @submit = Upload Avatar
    @endform

    <hr/>

    <h3>Choose a Preset Avatar</h3>
    <div class="alert alert-default">
        All that uploading business too complicated? Don't worry!
        Just click any of the avatars below to use it instantly.
    </div>

    {? $sel = $user->avatar_custom ? '' : $user->avatar_file; ?}
    @foreach ($avatar_groups as $title => $avatars)
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">{{ $title }} <span class="pull-right">Click an avatar to use it</span></h4>
            </div>
            <div class="panel-body">
                <div class="row avatar-preset-chooser">
                    @foreach ($avatars as $avatar)
                        <div class="col-md-2 col-sm-3 col-xs-6">
                            @form(panel/edit-avatar)
                                @hidden(id $user)
                                <input type="hidden" name="type" value="preset" />
                                <input type="hidden" name="preset" value="{{ $avatar }}" />
                                <button class="btn btn-{{ $avatar == $sel ? 'primary' : 'default' }}" type="submit">
                                    <img src="{{ asset('images/avatars/full/'.$avatar) }}" alt="Preset Avatar" />
                                </button>
                            @endform
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
@endsection