@extends('app')

@section('content')
    <h2>Add Competition Restriction</h2>
    @form(competition-restriction/create)
        @autocomplete(group_id api/competition-groups $group_id text=title) = Restriction Group
        @textarea(content_text) = Restriction Content
        @submit = Add Restriction
    @endform
@endsection
