@extends('app')

@section('content')
    <h2>
        Preview Competition Results: {{ $comp->name }}
    </h2>
    @if ($comp->results_intro_html)
        <div class="bbcode">{!! $comp->results_intro_html !!}</div>
    @endif
    <ul class="media-list">
        @foreach ($comp->getEntriesForResults() as $entry)
            <li class="media" data-id="{{ $entry->id }}">
                <div class="media-body">
                    <h3>
                        {{ $entry->title }} <small>By {{ $entry->user->name }}</small>
                    </h3>
                    {? $result = $comp->results->where('entry_id', $entry->id)->first(); ?}
                    @if ($result->rank == 1)
                        <h4>1st Place</h4>
                    @elseif ($result->rank == 2)
                        <h4>2nd Place</h4>
                    @elseif ($result->rank == 3)
                        <h4>3rd Place</h4>
                    @endif
                    <div class="bbcode">{!! $result->content_html !!}</div>
                </div>
                <div class="media-right">
                    {? $shot = $entry->screenshots->first(); ?}
                    <a href="#" class="gallery-button img-thumbnail">
                    @if ($shot)
                        <img src="{{ asset('uploads/competition/'.$shot->image_thumb) }}" alt="Screenshot" />
                    @else
                        <img src="{{ asset('images/no-screenshot-320.png') }}" alt="Screenshot" />
                    @endif
                    </a>
                </div>
            </li>
        @endforeach
    </ul>
    @if ($comp->results_outro_html)
        <div class="bbcode">{!! $comp->results_outro_html !!}</div>
    @endif
@endsection

@section('scripts')
    @include('competitions._gallery_javascript')
@endsection