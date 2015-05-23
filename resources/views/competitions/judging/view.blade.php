@extends('app')

@section('content')
    <h2>
        Competition Judging: {{ $comp->name }}
        <a href="{{ act('competition-judging', 'preview', $comp->id) }}" class="btn btn-success btn-xs"><span class="glyphicon glyphicon-eye-open"></span> Preview Results</a>
        <a href="{{ act('competition-judging', 'create-entry', $comp->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-plus"></span> Add Entry</a>
    </h2>
    <div class="alert alert-info">
        Once the judging is complete, contact a competition admin to publish the results.
    </div>
    {? $rank_values = [0 => 'No Rank', 1 => '1st Place', 2 => '2nd Place', 3 => '3rd Place']; ?}
    <ul class="media-list">
        @foreach ($comp->entries as $entry)
            <li class="media" data-id="{{ $entry->id }}">
                <div class="media-left">
                    {? $shot = $entry->screenshots->first(); ?}
                    <a href="#" class="gallery-button img-thumbnail">
                    @if ($shot)
                        <img src="{{ asset('uploads/competition/'.$shot->image_thumb) }}" alt="Screenshot" />
                    @else
                        <img src="{{ asset('images/no-screenshot-320.png') }}" alt="Screenshot" />
                    @endif
                    </a>
                </div>
                <div class="media-body">
                    <h3>
                        {{ $entry->title }} <small>By {{ $entry->user->name }}</small>
                        <a href="{{ act('competition-entry', 'manage', $entry->id) }}" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-picture"></span> Edit Screenshots</a>
                        <a href="{{ act('competition-judging', 'edit-entry', $entry->id) }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
                        <a href="{{ $entry->getLinkUrl() }}" class="btn btn-success btn-xs"><span class="glyphicon glyphicon-download-alt"></span> Download</a>
                    </h3>
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