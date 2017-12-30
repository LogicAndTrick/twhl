@title('Create competition restrictions')
@extends('app')

@section('content')
    <h1>Create competition restrictions</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
        <li><a href="{{ act('competition-restriction', 'index') }}">Restrictions</a></li>
        <li class="active">Create Restriction</li>
    </ol>

    @form(competition-restriction/create)
        @autocomplete(group_id api/competition-restriction-groups $group_id text=title) = Restriction Group
        <div class="wikicode-input">
            @textarea(content_text) = Restriction Content
        </div>
        @submit = Add Restriction
    @endform
@endsection
