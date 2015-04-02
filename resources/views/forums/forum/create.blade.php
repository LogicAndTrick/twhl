@extends('app')


@section('content')
    @form(forum/create)
        <h3>Create Forum</h3>
        @text(name:forum_name) = Name
        @text(slug) = URL Slug
        @text(description) = Description
        @submit
    @endform
@endsection