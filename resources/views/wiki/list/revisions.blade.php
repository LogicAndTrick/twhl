@extends('app')

@section('content')
    @include('wiki.nav', ['revision' => $revision])
    <h2>History: {{ $revision->title }}</h2>

    <table class="table table-bordered table-striped history">
        <thead>
            <tr>
                <th class="compare-column"></th>
                <th class="compare-column"></th>
                <th>Revision</th>
                <th>User</th>
                <th>Message</th>
                <th>Revert</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($history as $rev)
                <tr>
                    <td><input type="radio" name="compare1" value="{{ $rev->id }}" {{ $rev->id == $revision->id ? 'checked' : '' }} /></td>
                    <td><input type="radio" name="compare2" value="{{ $rev->id }}" {{ $rev->id == $next_id ? 'checked' : '' }} /></td>
                    <td><a href="{{ act('wiki', 'page', $rev->slug, $rev->id) }}">#{{ $rev->id }} - {{ Date::TimeAgo( $rev->created_at ) }}</a></td>
                    <td>{{ $rev->user->name }}</td>
                    <td>{{ $rev->message }}</td>
                    <td>
                        @if ($rev->id != $revision->id )
                            <a class="btn btn-primary" href="{{ act('wiki', 'revert', $rev->id) }}">Revert</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {!! $history->render() !!}

    <p>
        <button type="button" class="btn btn-info" id="compare-button">Compare selected revisions</button>
    </p>
    <div id="compare" class="diff-container"></div>
@endsection

@section('scripts')
    <script type="text/javascript">

        function get_revision(id) {
            return $.getJSON('{{ url("api/wiki-revisions") }}', { id: id, plain: true });
        }
        function compare_revisions(elem, id1, id2) {
            var r1 = get_revision(id1);
            var r2 = get_revision(id2);
            $.when(r1, r2).done(function (args1, args2) {
                var rev1 = args1[0][0], rev2 = args2[0][0];
                var v1 = difflib.stringAsLines(rev1.content_text);
                var v2 = difflib.stringAsLines(rev2.content_text);
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
            });
        }

        $('#compare-button').click(function() {
            var v1 = $('input:radio[name=compare1]:checked').val();
            var v2 = $('input:radio[name=compare2]:checked').val();
            if (v1 >= 0 && v2 >= 0 && v1 != v2) {
                compare_revisions($('#compare').html('<p>Loading...</p>'), Math.min(v1, v2), Math.max(v1, v2));
            }
        });
    </script>
@endsection