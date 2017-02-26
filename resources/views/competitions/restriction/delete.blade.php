@title('Delete competition restriction')
@extends('app')

@section('content')
    <h1>Delete competition restriction</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
        <li><a href="{{ act('competition-restriction', 'index') }}">Restrictions</a></li>
        <li class="active">Delete Restriction</li>
    </ol>

    @form(competition-restriction/delete)
        @hidden(id $restriction)
        <p>Are you sure you want to delete this restriction?</p>
        <div class="well bbcode">{!! $restriction->content_html !!}</div>
        @submit = Delete Restriction
    @endform
@endsection
