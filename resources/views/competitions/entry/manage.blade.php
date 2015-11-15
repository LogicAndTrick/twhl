@extends('app')

@section('content')
    <hc>
        <h1>Edit Entry Screenshots: {{ $entry->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
            <li class="active">Edit Screenshots</li>
        </ol>
    </hc>
    @if (count($entry->screenshots) > 0)
        <ul class="media-list screenshot-list">
            @foreach ($entry->screenshots as $shot)
                <li class="media">
                    <div class="media-left">
                        <img class="media-object" src="{{ asset('uploads/competition/'.$shot->image_thumb) }}" alt="Screenshot">
                    </div>
                    <div class="media-body">
                        <p>
                            <a href="{{ asset('uploads/competition/'.$shot->image_full) }}" target="_blank">See full image</a>
                        </p>
                        @form(competition-entry/delete-screenshot)
                            @hidden(id $shot)
                            <button type="submit" class="delete-button btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Delete</button>
                        @endform
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <p><em>You deleted all the screenshots! You should add some.</em></p>
    @endif

    <hr/>
    <h3>Add Screenshot</h3>
    @form(competition-entry/add-screenshot upload=true)
        @hidden(id $entry)
        @file(screenshot) = Screenshot
        @submit = Add Screenshot
    @endform
@endsection
