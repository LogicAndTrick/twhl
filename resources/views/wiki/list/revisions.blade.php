@title('History of wiki page: '.$revision->getNiceTitle())
@extends('app')

@section('content')
    @include('wiki.nav', ['revision' => $revision])

    <h1>
        <span class="fa fa-clock-o"></span>
        History of {{ $revision->getNiceTitle() }}
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ url('/wiki') }}">Wiki</a></li>
        <li><a href="{{ act('wiki', 'page', $revision->slug) }}">{{ $revision->getNiceTitle() }}</a></li>
        <li class="active">History</li>
    </ol>

    {!! $history->render() !!}

    {? $can_revert = $revision->wiki_object->canEdit(); ?}

    <table class="table table-bordered table-striped history">
        <thead>
            <tr>
                <th class="compare-column"></th>
                <th class="compare-column"></th>
                <th>Revision</th>
                <th>User</th>
                <th>Message</th>
                @if ($can_revert)
                    <th>Revert</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($history as $rev)
                <tr>
                    <td><input type="radio" name="compare1" value="{{ $rev->id }}" {{ $rev->id == $revision->id ? 'checked' : '' }} /></td>
                    <td><input type="radio" name="compare2" value="{{ $rev->id }}" {{ $rev->id == $next_id ? 'checked' : '' }} /></td>
                    <td><a href="{{ act('wiki', 'page', $rev->slug, $rev->id) }}">#{{ $rev->id }} - {{ Date::TimeAgo( $rev->created_at ) }}</a></td>
                    <td>@avatar($rev->user inline)</td>
                    <td>{{ $rev->message }}</td>
                    @if ($can_revert)
                        <td>
                            @if ($rev->id != $revision->id )
                                <a class="btn btn-primary btn-xs" href="{{ act('wiki', 'revert', $rev->id) }}">Revert</a>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>
        <button type="button" class="btn btn-info" id="compare-button">Compare selected revisions</button>
    </p>
    <div class="row diff-image-container">
        <div class="col-xs-6" id="compare-image-left"></div>
        <div class="col-xs-6" id="compare-image-right"></div>
    </div>
    <div id="compare" class="diff-container"></div>
@endsection

@section('scripts')
    <script type="text/javascript">

        function get_revision(id) {
            return $.getJSON('{{ url("api/wiki-revisions") }}', { id: id });
        }
        function get_revision_meta(id) {
            return $.getJSON('{{ url("api/wiki-revision-metas") }}', { revision_id: id, count: 100 });
        }
        function embed_image(container, rev) {
            container.append('<img src="{{ url("/wiki/embed/rev:") }}' + rev.id + '/current.png" alt="Image" />');
        }
        function extract_text(rev, meta) {
            var str = '';
            for (var i = 0; i < meta.length; i++) {
                var key = meta[i].key, val = meta[i].value;
                switch (key) {
                    case 'w':
                        str += '[META] Image Width: ' + val + '\n';
                        break;
                    case 'h':
                        str += '[META] Image Height: ' + val + '\n';
                        break;
                    case 's':
                        str += '[META] File Size: ' + val + '\n';
                        break;
                    case 'u':
                        str += '[META] Upload ID: ' + val + '\n';
                        break;
                }
            }
            str += rev.content_text;
            return str;
        }
        function compare_revisions(elem, left, right, id1, id2) {
            elem.html('<p>Loading...</p>');
            left.empty();
            right.empty();

            var r1 = get_revision(id1),
                m1 = get_revision_meta(id1),
                r2 = get_revision(id2),
                m2 = get_revision_meta(id2);

            $.when(r1, m1, r2, m2).done(function (args1, args2, args3, args4) {
                var rev1 = args1[0][0], rev2 = args3[0][0], meta1 = args2[0], meta2 = args4[0];
                var v1 = difflib.stringAsLines(extract_text(rev1, meta1));
                var v2 = difflib.stringAsLines(extract_text(rev2, meta2));
                var sm = new difflib.SequenceMatcher(v1, v2);
                $(elem).html(diffview.buildView({
                    baseTextLines: v1,
                    newTextLines: v2,
                    opcodes: sm.get_opcodes(),
                    baseTextName: "Revision "+id1,
                    newTextName: "Revision "+id2,
                    contextSize: 3,
                    viewType: 0
                }));
                if ({{$object->type_id == \App\Models\Wiki\WikiType::UPLOAD ? 'true' : 'false'}}) {
                    embed_image(left, rev1);
                    embed_image(right, rev2);
                }
            });
        }

        $('#compare-button').click(function() {
            var v1 = $('input:radio[name=compare1]:checked').val();
            var v2 = $('input:radio[name=compare2]:checked').val();
            if (v1 >= 0 && v2 >= 0 && v1 != v2) {
                compare_revisions(
                    $('#compare'),
                    $('#compare-image-left'),
                    $('#compare-image-right'),
                    Math.min(v1, v2),
                    Math.max(v1, v2));
            }
        });
    </script>
@endsection