@extends('app')

@section('content')
    <hc>
        <h1>Delete Competition Restrictions</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li><a href="{{ act('competition-restriction', 'index') }}">Restrictions</a></li>
            <li class="active">Delete Restriction</li>
        </ol>
    </hc>
    <h2>Delete Competition Restriction </h2>
    @form(competition-restriction/delete)
        @hidden(id $restriction)
        <p>Are you sure you want to delete this restriction?</p>
        <div class="well bbcode">{!! $restriction->content_html !!}</div>
        @submit = Delete Restriction
    @endform
@endsection
