@extends('app')

@section('content')
    <hc>
        <h1>Delete Competition Restriction Group: {{ $group->title }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li><a href="{{ act('competition-restriction', 'index') }}">Restrictions</a></li>
            <li class="active">Delete Group</li>
        </ol>
    </hc>
    @form(competition-group/delete)
        @hidden(id $group)
        <p>Are you sure you want to delete this restriction group?</p>
        @submit = Delete Group
    @endform
@endsection
