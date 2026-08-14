@title('History of wiki page: '.$revision->getNiceTitle())
@extends('app')

@section('content')
    @include('wiki.nav', ['revision' => $revision])

    <h1>
        <span class="fa fa-clock-o"></span>
        History of {{ $revision->getNiceTitle() }}
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('wiki', 'index') }}">Wiki</a></li>
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
        <div class="col-6" id="compare-image-left"></div>
        <div class="col-6" id="compare-image-right"></div>
    </div>
    <div id="compare" class="diff-container"></div>
@endsection

@section('scripts')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', () => {
            const compareEl = document.getElementById('compare');
            const compareButton = document.getElementById('compare-button');
            compareButton.addEventListener('click', () => {
                const v1 = document.querySelector('input[type="radio"][name="compare1"]:checked').value;
                const v2 = document.querySelector('input[type="radio"][name="compare2"]:checked').value;
                window.attachWikiRevisionDiff?.call(window, compareEl, Math.min(v1, v2), Math.max(v1, v2));
            });
        });
    </script>
@endsection