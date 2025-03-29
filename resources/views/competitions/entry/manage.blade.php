@title('Edit competition entry screenshots: '.$entry->name)
@extends('app')

@section('content')
    <h1>Edit entry screenshots: {{ $entry->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
        <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
        <li class="active">Edit Screenshots</li>
    </ol>

    @if (count($entry->screenshots) > 0)
        <ul class="list-unstyled screenshot-list container">
            @foreach ($entry->screenshots as $shot)
                <li class="row mb-3">
                    <div class="col-3">
                        <img src="{{ asset('uploads/competition/'.$shot->image_thumb) }}" alt="Screenshot">
                    </div>
                    <div class="col-9">
                        <p>
                            <a href="{{ asset('uploads/competition/'.$shot->image_full) }}" target="_blank">See full image</a>
                        </p>
                        @form(competition-entry/delete-screenshot)
                            @hidden(id $shot)
                            <button type="submit" class="delete-button btn btn-danger btn-xs"><span class="fa fa-remove"></span> Delete</button>
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
