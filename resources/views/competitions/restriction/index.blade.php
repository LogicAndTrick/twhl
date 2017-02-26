@title('Competition restrictions')
@extends('app')

@section('content')
    <h1>Competition restrictions</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
        <li class="active">Restrictions</li>
    </ol>

    @foreach ($groups as $group)
        <h3>
            {{ $group->title }}
            <small>{{ $group->is_multiple ? 'Multiple selection' : 'Single selection' }}</small>
            <a href="{{ act('competition-group', 'delete', $group->id) }}" class="btn btn-danger btn-xs"><span class="fa fa-remove"></span></a>
            <a href="{{ act('competition-group', 'edit', $group->id) }}" class="btn btn-primary btn-xs"><span class="fa fa-pencil"></span></a>
        </h3>
        <ul>
            @foreach ($group->restrictions as $rest)
                <li>
                    <div class="bbcode d-inline-block mb-0 align-text-bottom">{!! $rest->content_html !!}</div>
                    <a href="{{ act('competition-restriction', 'edit', $rest->id) }}" class="btn btn-minimal btn-xxs"><span class="fa fa-pencil"></a>
                    <a href="{{ act('competition-restriction', 'delete', $rest->id) }}" class="btn btn-minimal btn-xxs"><span class="fa fa-remove"></a>
                </li>
            @endforeach
            <li>
                <a href="{{ act('competition-restriction', 'create', $group->id) }}" class="btn btn-minimal btn-xs"><span class="fa fa-plus"></span> Add Restriction</a>
            </li>
        </ul>
    @endforeach

    <hr />

    <h3>Add Group</h3>
    @form(competition-group/create)
        @text(title) = Group Title
        @checkbox(is_multiple) = Allow Multiple Selection
        @submit = Create Group
    @endform
@endsection
