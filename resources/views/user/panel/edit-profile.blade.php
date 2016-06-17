@title('Update profile')
@extends('app')

@section('content')
    <hc>
        <h1>Update profile: {{ $user->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
            <li class="active">Update Profile</li>
        </ol>
    </hc>

    @form(panel/edit-profile)
        @hidden(id $user)
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Custom Title</h3>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-info">If enabled, the custom title appears underneath your avatar on your profile and forum posts.</div>
                        @checkbox(title_custom $user) = Enable Custom Title
                        @text(title_text $user) = Custom Title Text
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Skills</h3>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-info">Which of these skills are you experienced with?</div>
                        @checkbox(skill_map $user) = Mapping
                        @checkbox(skill_model $user) = Modelling
                        @checkbox(skill_code $user) = Programming
                        @checkbox(skill_music $user) = Music/Sound Effects
                        @checkbox(skill_voice $user) = Voice Acting
                        @checkbox(skill_animate $user) = Model Animation
                        @checkbox(skill_texture $user) = Texture Creation
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Information</h3>
            </div>
            <div class="panel-body">
                <div class="alert alert-info">Tell everyone a little bit about yourself. If you don't want to fill something in, just leave it blank!</div>
                <div class="row">
                    <div class="col-md-6">
                        @text(info_name $user) = Your real name
                        @text(info_website $user) = Website/blog URL
                        @text(info_occupation $user) = Your occupation (or field of study)
                        @text(info_interests $user) = What interests you
                    </div>
                    <div class="col-md-6">
                        @text(info_location $user) = Where you live
                        @text(info_languages $user) = What languages you speak
                        @text(info_birthday_formatted $user) = Your birthday (format: DD/MM)
                        @text(info_steam_profile $user) = Your Steam name
                        <p class="help-block">To find your Steam name, go to "Edit Profile" in Steam. Set up a custom URL and enter the same value in the box above.</p>
                    </div>
                    <div class="col-md-6"></div>
                </div>
                @textarea(info_biography_text $user class=medium) = Enter any additional interesting information here
                <div class="form-group">
                    <h4>
                        Formatted preview
                        <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
                    </h4>
                    <div id="preview-panel" class="well bbcode"></div>
                </div>
            </div>
        </div>
        @submit = Save Profile
    @endform
@endsection

@section('scripts')
    <script type="text/javascript">
        $('#update-preview').click(function() {
            $('#preview-panel').html('Loading...');
            $.post('{{ url("api/posts/format") }}?field=info_biography_text', $('form').serializeArray(), function(data) {
                $('#preview-panel').html(data);
            });
        });
    </script>
@endsection