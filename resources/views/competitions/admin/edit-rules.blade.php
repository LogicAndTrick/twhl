@extends('app')

@section('content')
    <h2>Edit Competition Rules: {{ $comp->name }}</h2>

    @form(competition-admin/edit-rules)
        @hidden(id $comp)

        @foreach ($groups as $group)
            <h4>{{ $group->title }}</h4>
            <ul style="list-style-type: none;">
                @if (!$group->is_multiple)
                    <li>
                        <label class="radio">
                            <input name="restrictions[{{ $group->id }}][]" type="radio" value="-1" {{ !$comp->hasRestrictionInGroup($group->id) ? 'checked' : '' }} />
                            <em>None of the below</em>
                        </label>
                    </li>
                @endif
                @foreach ($group->restrictions as $rest)
                    <li>
                        <label class="{{ $group->is_multiple ? 'checkbox' : 'radio' }}" style="font-weight: normal;">
                            <input name="restrictions[{{ $group->id }}][]" type="{{ $group->is_multiple ? 'checkbox' : 'radio' }}" value="{{ $rest->id }}" {{ $comp->hasRestriction($rest->id) ? 'checked' : '' }} />
                            <span class="bbcode">{!! $rest->content_html !!}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
        @endforeach

        @submit = Submit
    @endform
@endsection
