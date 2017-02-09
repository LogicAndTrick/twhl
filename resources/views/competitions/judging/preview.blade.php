@title('Preview competition results: '.$comp->name)
@extends('app')

@section('content')
    <hc>
        <h1>Preview results: {{ $comp->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
            <li><a href="{{ act('competition-judging', 'view', $comp->id) }}">Judging Panel</a></li>
            <li class="active">Preview Results</li>
        </ol>
    </hc>
    @if ($comp->isJudged() && $comp->judges->count() > 0)
        <p>
            Judged By:
            {? $i = 0; ?}
            @foreach ($comp->judges as $judge)
                {!! $i++ == 0 ? '' : ' &bull; ' !!}
                @avatar($judge inline)
            @endforeach
        </p>
    @endif
    @if ($comp->results_intro_html)
        <div class="bbcode">{!! $comp->results_intro_html !!}</div>
    @endif
    <ul class="media-list">
        {? $prev_rank = -1; ?}
        @foreach ($comp->getEntriesForResults() as $entry)
            {? $result = $comp->results->where('entry_id', $entry->id)->first(); ?}
            @if ($prev_rank != 0 && $result->rank == 0)
                </ul>
                <hr/>
                <h3>Other Entries</h3>
                <ul class="media-list">
            @endif
            {? $prev_rank = $result->rank; ?}
            <li class="media" data-id="{{ $entry->id }}" data-title="{{ ($entry->title ? $entry->title : 'Unnamed entry') . ' - ' . $entry->user->name }}">
                <div class="media-body">
                    <h3>
                        {{ $entry->title }} &mdash; By @avatar($entry->user inline)
                        <a href="{{ $entry->getLinkUrl() }}" class="btn btn-success btn-xs"><span class="fa fa-download"></span> Download</a>
                    </h3>
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
                    <a href="#" class="gallery-button img-thumbnail tagged">
                        @if ($shot)
                            <img class="media-object" src="{{ asset('uploads/competition/'.$shot->image_thumb) }}" alt="Screenshot" />
                        @else
                            <img class="media-object" src="{{ asset('images/no-screenshot-320.png') }}" alt="Screenshot" />
                        @endif
                        @if ($result->rank == 1)
                            <span class="tag"><span class="fa fa-star"></span> 1st Place</span>
                        @elseif ($result->rank == 2)
                            <span class="tag"><span class="fa fa-star"></span> 2nd Place</span>
                        @elseif ($result->rank == 3)
                            <span class="tag"><span class="fa fa-star"></span> 3rd Place</span>
                        @endif
                    </a>
                    @if ($entry->screenshots->count() > 1)
                        <button class="btn btn-info btn-block gallery-button" type="button">
                            <span class="fa fa-picture-o"></span>
                            + {{ $entry->screenshots->count()-1 }} more screenshot{{ $entry->screenshots->count() == 2 ? '' : 's' }}
                        </button>
                    @endif
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