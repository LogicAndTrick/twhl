@title('Create forum')
@extends('app')

@section('content')
    <h1>Create forum</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
        <li class="active">Create</li>
    </ol>

    @form(forum/create)
        <h3>Create Forum</h3>
        @text(name:forum_name) = Name
        @text(slug) = URL Slug
        <div class="wikicode-input">
            @textarea(description) = Description
        </div>
        @autocomplete(permission_id api/permissions clearable=true) = Required Permission
        @submit = Create Forum
    @endform
@endsection
