@extends('app')

@section('content')
    <h2>Edit Competition Restriction</h2>
    @form(competition-restriction/edit)
        @hidden(id $restriction)
        @autocomplete(group_id api/competition-groups $restriction text=title) = Restriction Group
        @textarea(content_text $restriction) = Restriction Content
        @submit = Edit Restriction
    @endform
@endsection
