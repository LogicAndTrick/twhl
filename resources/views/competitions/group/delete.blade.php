@title('Delete competition restriction group: '.$group->title)
@extends('app')

@section('content')
    <h1>Delete competition restriction group: {{ $group->title }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
        <li><a href="{{ act('competition-restriction', 'index') }}">Restrictions</a></li>
        <li class="active">Delete Group</li>
    </ol>

    @form(competition-group/delete)
        @hidden(id $group)
        <p>Are you sure you want to delete this restriction group?</p>
        @submit = Delete Group
    @endform
@endsection
