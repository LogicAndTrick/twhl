@title('Revert wiki page')
@extends('app')

@section('content')
    @include('wiki.nav', ['revision' => $revision])

    <h1>Reverting: {{ $revision->getNiceTitle() }} <small>(to: {{ Date::TimeAgo($revision->created_at) }})</small></h1>

    <ol class="breadcrumb">
        <li><a href="{{ url('/wiki') }}">Wiki</a></li>
        <li><a href="{{ act('wiki', 'page', $revision->slug) }}">{{ $revision->getNiceTitle() }}</a></li>
        <li><a href="{{ act('wiki', 'history', $revision->slug) }}">History</a></li>
        <li class="active">Revert Page</li>
    </ol>

    <div class="card card-block">
        @form(wiki/revert)
            @hidden(id $revision)
            <p>Continuing will revert this page to the revision displayed below. Are you sure you want to continue?</p>
            @text(reason) = Reason for revert
            @submit = Revert to this Revision
        @endform
    </div>
    @if ($revision->wiki_object->type_id == \App\Models\Wiki\WikiType::UPLOAD)
        <div class="wiki-image">
            <img src="{{ $revision->getUpload()->getResourceFileName() }}" alt="{{ $revision->getNiceTitle() }}">
        </div>
    @endif
    <div class="card mt-3">
        <div class="card-header">
          Page appearance after reverting
        </div>
        <div class="card-block">
            <div class="bbcode">{!! $revision->content_html !!}</div>
            @include('wiki.view.revision-categories', ['revision' => $revision])
        </div>
    </div>
@endsection
