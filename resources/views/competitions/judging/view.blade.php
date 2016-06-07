@title('Competition judging: '.$comp->name)
@extends('app')

@section('content')
    <hc>
        @if (permission('CompetitionAdmin') && $comp->isJudging())
            <a href="{{ act('competition-judging', 'publish', $comp->id) }}" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-arrow-right"></span> Publish Results</a>
        @endif
        <a href="{{ act('competition-judging', 'preview', $comp->id) }}" class="btn btn-success btn-xs"><span class="glyphicon glyphicon-eye-open"></span> Preview Results</a>
        <a href="{{ act('competition-judging', 'create-entry', $comp->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-plus"></span> Add Entry</a>
        <h1>Competition judging: {{ $comp->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
            <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
            <li class="active">Judging Panel</li>
        </ol>
    </hc>
    <div class="alert alert-info">
        Once the judging is complete, contact a competition admin to publish the results.
    </div>
    {? $rank_values = [0 => 'No Rank', 1 => '1st Place', 2 => '2nd Place', 3 => '3rd Place']; ?}
    <ul class="media-list">
        @foreach ($comp->getEntriesForJudging() as $entry)
            <li class="media" data-id="{{ $entry->id }}">
                <div class="media-left">
                    {? $shot = $entry->screenshots->first(); ?}
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
                    @if ($comp->isVoted())
                        <div class="text-center">
                            <h2>Votes: {{ $comp->countVotesFor($entry->id) }}</h2>
                        </div>
                    @endif
                </div>
                <div class="media-body">
                    <h3>
                        {{ $entry->title }} &mdash; By @avatar($entry->user inline)
                        @if (permission('CompetitionAdmin'))
                            <a href="{{ act('competition-entry', 'delete', $entry->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Delete Entry</a>
                        @endif
                        <a href="{{ act('competition-entry', 'manage', $entry->id) }}" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-picture"></span> Edit Screenshots</a>
                        <a href="{{ act('competition-judging', 'edit-entry', $entry->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
                        <a href="{{ $entry->getLinkUrl() }}" class="btn btn-success btn-xs"><span class="glyphicon glyphicon-download-alt"></span> Download</a>
                    </h3>
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="bbcode">{!! $entry->content_html !!}</div>
                        </div>
                    </div>
                    <hr/>
                    {? $result = $comp->results->where('entry_id', $entry->id)->first(); ?}
                    <div>
                        @form(competition-judging/edit)
                            @hidden(id $entry)
                            @select(rank $rank_values $result ) = Rank
                            @textarea(content_text $result class=tiny) = Review Text
                            @submit = Save
                        @endform
                    </div>
                </div>
                <hr/>
            </li>
        @endforeach
    </ul>
@endsection

@section('scripts')
    @include('competitions._gallery_javascript')
    <script type="text/javascript">
        var edit_url = "{{ url('competition-judging', 'edit') }}";
        $(function() {
            $('form').submit(function(event) {
                event.preventDefault();
                var $t = $(this).addClass('loading');
                $.post(edit_url, $t.serializeArray()).always(function() {
                    $t.removeClass('loading');
                });
            });
        });
    </script>
@endsection