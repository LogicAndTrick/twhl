@title('Edit Competition Rules: '.$comp->name)
@extends('app')

@section('content')
    <hc>
        <h1>Edit competition rules: {{ $comp->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
            <li class="active">Edit Rules</li>
        </ol>
    </hc>

    @form(competition-admin/edit-rules)
        @hidden(id $comp)

        @foreach ($groups as $group)
            <h4>{{ $group->title }}</h4>
            <ul style="list-style-type: none;">
                @if (!$group->is_multiple)
                    <li class="form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" name="restrictions[{{ $group->id }}][]" type="radio" value="-1" {{ !$comp->hasRestrictionInGroup($group->id) ? 'checked' : '' }} />
                            <em>None of the below</em>
                        </label>
                    </li>
                @endif
                @foreach ($group->restrictions as $rest)
                    <li class="form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" name="restrictions[{{ $group->id }}][]" type="{{ $group->is_multiple ? 'checkbox' : 'radio' }}" value="{{ $rest->id }}" {{ $comp->hasRestriction($rest->id) ? 'checked' : '' }} />
                            <div class="bbcode mb-0 d-inline">{!! $rest->content_html !!}</div>
                        </label>
                    </li>
                @endforeach
            </ul>
        @endforeach

        @submit = Edit Rules
    @endform
@endsection
