@title('Competition results: '.$comp->name)
@extends('app')

@section('content')
    <hc>
        @if (permission('CompetitionAdmin'))
            <a href="{{ act('competition-admin', 'delete', $comp->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Delete</a>
            <a href="{{ act('competition-admin', 'edit-rules', $comp->id) }}" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-list-alt"></span> Edit Rules</a>
            <a href="{{ act('competition-admin', 'edit', $comp->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
        @endif
        <h1>Competition results: {{ $comp->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
            <li class="active">View Results</li>
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
            {? $shot = $entry->screenshots->first(); ?}
            {? $prev_rank = $result->rank; ?}
            <li class="media media-panel" data-id="{{ $entry->id }}" data-title="{{ ($entry->title ? $entry->title : 'Unnamed entry') . ' - ' . $entry->user->name }}">
                <div class="media-heading">
                    {? $result = $comp->results->where('entry_id', $entry->id)->first(); ?}
                    @if ($result->rank == 1)
                        <h2>1st Place</h2>
                    @elseif ($result->rank == 2)
                        <h2>2nd Place</h2>
                    @elseif ($result->rank == 3)
                        <h2>3rd Place</h2>
                    @endif
                    <h3>{{ $entry->title }}</h3>
                    <h5>
                        By @avatar($entry->user inline)
                        @if ($entry->file_location)
                            <a href="{{ $entry->getLinkUrl() }}" class="btn btn-success btn-xs"><span class="glyphicon glyphicon-download-alt"></span> Download</a>
                        @endif
                    </h5>
                </div>
                <div class="media-body">
                    <div class="visible-sm-block visible-xs-block text-center">
                        <div style="display: inline-block;">
                            <a href="#" class="gallery-button img-thumbnail">
                                @if ($shot)
                                    <img class="media-object" src="{{ asset('uploads/competition/'.$shot->image_thumb) }}" alt="Screenshot" />
                                @else
                                    <img class="media-object" src="{{ asset('images/no-screenshot-320.png') }}" alt="Screenshot" />
                                @endif
                            </a>
                            @if ($entry->screenshots->count() > 1)
                                <button class="btn btn-info btn-block gallery-button" type="button">
                                    <span class="glyphicon glyphicon-picture"></span>
                                    + {{ $entry->screenshots->count()-1 }} more screenshot{{ $entry->screenshots->count() == 2 ? '' : 's' }}
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="bbcode">{!! $result->content_html !!}</div>
                </div>
                <div class="media-right hidden-xs hidden-sm">
                    <a href="#" class="gallery-button img-thumbnail">
                        @if ($shot)
                            <img class="media-object" src="{{ asset('uploads/competition/'.$shot->image_thumb) }}" alt="Screenshot" />
                        @else
                            <img class="media-object" src="{{ asset('images/no-screenshot-320.png') }}" alt="Screenshot" />
                        @endif
                    </a>
                    @if ($entry->screenshots->count() > 1)
                        <button class="btn btn-info btn-block gallery-button" type="button">
                            <span class="glyphicon glyphicon-picture"></span>
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