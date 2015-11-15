@extends('app')

@section('content')
    <hc>
        <h1>Edit Competition Restriction Group: {{ $group->title }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li><a href="{{ act('competition-restriction', 'index') }}">Restrictions</a></li>
            <li class="active">Edit Group</li>
        </ol>
    </hc>
    @form(competition-group/edit)
        @hidden(id $group)
        @text(title $group) = Group Title
        @checkbox(is_multiple $group) = Allow Multiple Selection
        @submit = Edit Group
    @endform
@endsection
