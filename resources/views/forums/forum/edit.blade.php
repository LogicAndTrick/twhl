@title('Edit Forum: '.$forum->name)
@extends('app')

@section('content')
    <hc>
        <h1>Edit Forum: {{ $forum->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            <li class="active">Edit</li>
        </ol>
    </hc>
    @form(forum/edit)
        @hidden(id $forum)
        @text(name:forum_name $forum) = Name
        @text(slug $forum) = URL Slug
        @text(description $forum) = Description
        @autocomplete(permission_id api/permissions $forum clearable=true) = Required Permission
        @submit
    @endform
@endsection
