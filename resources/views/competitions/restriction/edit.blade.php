@title('Edit competition restriction')
@extends('app')

@section('content')
    <hc>
        <h1>Edit competition restriction</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li><a href="{{ act('competition-restriction', 'index') }}">Restrictions</a></li>
            <li class="active">Edit Restriction</li>
        </ol>
    </hc>
    @form(competition-restriction/edit)
        @hidden(id $restriction)
        @autocomplete(group_id api/competition-groups $restriction text=title) = Restriction Group
        @textarea(content_text $restriction) = Restriction Content
        @submit = Edit Restriction
    @endform
@endsection
