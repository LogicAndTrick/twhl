@title('Competition judging: '.$comp->name)
@extends('app')

@section('content')
    <h1>
        Competition judging: {{ $comp->name }}

        @if (permission('CompetitionAdmin') && $comp->isJudging())
            <a href="{{ act('competition-judging', 'publish', $comp->id) }}" class="btn btn-outline-info btn-xs"><span class="fa fa-arrow-right"></span> Publish Results</a>
        @endif
        <a href="{{ act('competition-judging', 'preview', $comp->id) }}" class="btn btn-success btn-xs"><span class="fa fa-eye"></span> Preview Results</a>
        <a href="{{ act('competition-judging', 'create-entry', $comp->id) }}" class="btn btn-primary btn-xs"><span class="fa fa-plus"></span> Add Entry</a>
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('competition', 'index') }}">Competitions</a></li>
        <li><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name}}</a></li>
        <li class="active">Judging Panel</li>
    </ol>

    <div class="alert alert-info">
        Once the judging is complete, contact a competition admin to publish the results.
    </div>

    {? $rank_values = [0 => 'No Rank', 1 => '1st Place', 2 => '2nd Place', 3 => '3rd Place']; ?}
    <div>
        @foreach ($comp->getEntriesForJudging() as $entry)
            <div class="slot" data-id="{{ $entry->id }}" data-title="{{ ($entry->title ? $entry->title : 'Unnamed entry') . ' - ' . $entry->user->name }}">
                <div class="slot-heading">
                    <div class="slot-avatar">
                        @avatar($entry->user small show_name=false)
                    </div>
                    <div class="slot-title">
                        @avatar($entry->user text)

                        @if (permission('CompetitionAdmin'))
                            <a href="{{ act('competition-entry', 'delete', $entry->id) }}" class="btn btn-danger btn-xs"><span class="fa fa-remove"></span> Delete Entry</a>
                        @endif
                        <a href="{{ act('competition-entry', 'manage', $entry->id) }}" class="btn btn-info btn-xs"><span class="fa fa-picture-o"></span> Edit Screenshots</a>
                        <a href="{{ act('competition-judging', 'edit-entry', $entry->id) }}" class="btn btn-primary btn-xs"><span class="fa fa-pencil"></span> Edit</a>
                        @if ($entry->getLinkUrl())
                            <a href="{{ $entry->getLinkUrl() }}" class="btn btn-success btn-xs"><span class="fa fa-download"></span> Download</a>
                        @endif
                    </div>
                    <div class="slot-subtitle">
                        {{ $entry->title }}
                    </div>
                </div>

                {? $shot = $entry->screenshots->first(); ?}

                <div class="slot-main d-flex flex-column flex-md-row">

                    <div class="text-center mr-md-3">
                        <a href="#" class="gallery-button img-thumbnail">
                            <img class="main" src="{{asset( $shot ? 'uploads/competition/'.$shot->image_thumb : 'images/no-screenshot-320.png' ) }}" alt="Entry">
                            @foreach($entry->screenshots->slice(1, 3) as $sh)
                                <span class="preview" style="background-image: url('{{asset( $shot ? 'uploads/competition/'.$sh->image_thumb : 'images/no-screenshot-320.png' ) }}');">
                                </span>
                            @endforeach
                            @if ($entry->screenshots->count() > 4)
                                <span class="more">+{{ $entry->screenshots->count() - 4 }}</span>
                            @endif
                        </a>
                        @if ($comp->isVoted())
                            <div class="text-center">
                                <strong>Votes: {{ $comp->countVotesFor($entry->id) }}</strong>
                            </div>
                        @endif
                    </div>

                    <div class="w-100">
                        <div class="bbcode">
                            {!! $entry->content_html ? $entry->content_html : '<em>No Description</em>' !!}
                        </div>

                        <hr/>

                        {? $result = $comp->results->where('entry_id', $entry->id)->first(); ?}
                        <div>
                            @form(competition-judging/edit)
                                @hidden(id $entry)
                                @select(rank $rank_values $result ) = Rank
                                <div class="wikicode-input">
                                    @textarea(content_text $result class=tiny) = Review Text
                                </div>
                                @submit = Save
                            @endform
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
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