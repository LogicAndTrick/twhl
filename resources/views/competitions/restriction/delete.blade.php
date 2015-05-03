@extends('app')

@section('content')
    <h2>Delete Competition Restriction </h2>
    @form(competition-restriction/delete)
        @hidden(id $restriction)
        <p>Are you sure you want to delete this restriction?</p>
        <div class="well bbcode">{!! $restriction->content_html !!}</div>
        @submit = Delete Restriction
    @endform
@endsection
