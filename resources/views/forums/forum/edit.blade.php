@extends('app')

@section('content')
    @form(forum/edit)
        <h3>Edit Forum</h3>
        @hidden(id $forum)
        @text(name:forum_name $forum) = Name
        @text(slug $forum) = URL Slug
        @text(description $forum) = Description
        @autocomplete(permission_id api/permissions $forum clearable=true) = Required Permission
        @submit
    @endform
@endsection
