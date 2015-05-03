@extends('app')

@section('content')
    <h2>Competition Restriction Group: {{ $group->title }}</h2>
    @form(competition-group/edit)
        @hidden(id $group)
        @text(title $group) = Group Title
        @checkbox(is_multiple $group) = Allow Multiple Selection
        @submit = Edit Group
    @endform
@endsection
