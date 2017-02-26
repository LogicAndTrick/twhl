@title('Edit competition restriction group: '.$group->title)
@extends('app')

@section('content')
    <h1>Edit competition restriction group: {{ $group->title }}</h1>
    <ol class="breadcrumb">
        <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
        <li><a href="{{ act('competition-restriction', 'index') }}">Restrictions</a></li>
        <li class="active">Edit Group</li>
    </ol>

    @form(competition-group/edit)
        @hidden(id $group)
        @text(title $group) = Group Title
        @checkbox(is_multiple $group) = Allow Multiple Selection
        @submit = Edit Group
    @endform
@endsection
