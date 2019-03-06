<a id="results"></a>
<h1>
    Results
    @if ($comp->isJudged() && $comp->judges->count() > 0)
        <small class="pull-right">
            Judged By:
            {? $i = 0; ?}
            @foreach ($comp->judges as $judge)
                {!! $i++ == 0 ? '' : ' &bull; ' !!}
                @avatar($judge inline)
            @endforeach
        </small>
    @endif
</h1>

<div class="competition-results">
    @if ($comp->results_intro_html)
        <div class="bbcode">{!! $comp->results_intro_html !!}</div>
    @endif
    <div>
        {? $prev_rank = -1; ?}
        @foreach ($comp->getEntriesForResults() as $entry)
            {? $result = $comp->results->where('entry_id', $entry->id)->first(); ?}
            @if ($prev_rank != 0 && $result->rank == 0)
                </div>
                <h1>Other Entries</h1>
                <div>
            @endif
            {? $shot = $entry->screenshots->first(); ?}
            {? $prev_rank = $result->rank; ?}
            {? $result = $comp->results->where('entry_id', $entry->id)->first(); ?}
            <div class="slot rank-{{ $result->rank }}" data-id="{{ $entry->id }}" data-title="{{ ($entry->title ? $entry->title : 'Unnamed entry') . ' - ' . $entry->user->name }}">
                <div class="slot-heading">
                    <div class="pull-right mt-2 hidden-xs-down">
                        @if ($entry->file_location)
                            <a href="{{ $entry->getLinkUrl() }}" class="btn btn-success btn-xs"><span class="fa fa-download"></span> Download</a>
                        @endif
                    </div>
                    <div class="slot-avatar">
                        @avatar($entry->user small show_name=false)
                    </div>
                    <div class="slot-title">
                        @if ($result->rank == 1)
                            1st Place -
                        @elseif ($result->rank == 2)
                            2nd Place -
                        @elseif ($result->rank == 3)
                            3rd Place -
                        @endif
                        @avatar($entry->user text)
                    </div>
                    <div class="slot-subtitle">
                        {{ $entry->title ? $entry->title : 'Unnamed entry' }}
                    </div>
                </div>
                <div class="slot-main">
                    @if ($entry->file_location)
                        <div class="my-2 hidden-sm-up text-center">
                            <a href="{{ $entry->getLinkUrl() }}" class="btn btn-success btn-xs"><span class="fa fa-download"></span> Download</a>
                        </div>
                    @endif
                    <div class="d-flex flex-column flex-lg-row">
                        <div class="text-center order-lg-2 my-2 ml-0 ml-lg-3 my-lg-0">
                            <div style="display: inline-block;">
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
                            </div>
                        </div>
                        <div class="bbcode">{!! $result->content_html !!}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @if ($comp->results_outro_html)
        <div class="bbcode">{!! $comp->results_outro_html !!}</div>
    @endif
</div>