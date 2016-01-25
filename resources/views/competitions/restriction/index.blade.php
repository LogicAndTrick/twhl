@title('Competition Restrictions')
@extends('app')

@section('content')
    <hc>
        <h1>Competition Restrictions</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li class="active">Restrictions</li>
        </ol>
    </hc>
    @foreach ($groups as $group)
        <h3>
            {{ $group->title }}
            <small>{{ $group->is_multiple ? 'Multiple selection' : 'Single selection' }}</small>
            <a href="{{ act('competition-group', 'delete', $group->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span></a>
            <a href="{{ act('competition-group', 'edit', $group->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span></a>
        </h3>
        <ul>
            @foreach ($group->restrictions as $rest)
                <li>
                    <span class="bbcode">{!! $rest->content_html !!}</span>
                    <a href="{{ act('competition-restriction', 'edit', $rest->id) }}" class="btn btn-minimal btn-xxs"><span class="glyphicon glyphicon-pencil"></a>
                    <a href="{{ act('competition-restriction', 'delete', $rest->id) }}" class="btn btn-minimal btn-xxs"><span class="glyphicon glyphicon-remove"></a>
                </li>
            @endforeach
            <li>
                <a href="{{ act('competition-restriction', 'create', $group->id) }}" class="btn btn-minimal btn-xs"><span class="glyphicon glyphicon-plus"></span> Add Restriction</a>
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
