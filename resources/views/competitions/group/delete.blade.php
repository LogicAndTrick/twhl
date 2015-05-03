@extends('app')

@section('content')
    <h2>Competition Restriction Group: {{ $group->title }}</h2>
    @form(competition-group/delete)
        @hidden(id $group)
        <p>Are you sure you want to delete this restriction group?</p>
        @submit = Delete Group
    @endform
@endsection
