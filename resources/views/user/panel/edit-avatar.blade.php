@title('Update avatar')
@extends('app')

@section('content')
    <h1>Update avatar: {{ $user->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
        <li class="active">Update Avatar</li>
    </ol>

    <div class="alert alert-info">You can choose to upload your own avatar or alternatively, you can use one of our presets.</div>

    <div class="text-center">
        <p>Current avatar:</p>
        <img class="img-thumbnail" src="{{ $user->avatar_full }}" alt="Avatar">
    </div>

    <h2>Upload Custom Avatar</h2>

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
        @file(upload) = Choose Image (avif, gif, jpg, png, or webp)
        @submit = Upload Avatar
    @endform

    <hr/>

    <h2>Choose a Preset Avatar</h2>
    <div class="card card-body">
        All that uploading business too complicated? Don't worry!
        Just click any of the avatars below to use it instantly.
    </div>

    {? $sel = $user->avatar_custom ? '' : $user->avatar_file; ?}
    @foreach ($avatar_groups as $title => $avatars)
        <div class="card mt-3">
            <div class="card-header">
                {{ $title }} <span class="pull-right">Click an avatar to use it</span>
            </div>
            <div class="card-body">
                <div class="row avatar-preset-chooser">
                    @foreach ($avatars as $avatar)
                        <div class="col-md-2 col-sm-3 col-xs-6 mb-3">
                            @form(panel/edit-avatar)
                                @hidden(id $user)
                                <input type="hidden" name="type" value="preset" />
                                <input type="hidden" name="preset" value="{{ $avatar }}" />
                                <button class="btn btn-{{ $avatar == $sel ? 'primary' : 'outline-secondary' }}" style="cursor: pointer;" type="submit">
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