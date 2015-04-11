@extends('app')

@section('content')
    @include('wiki.nav', ['revision' => $revision])

    <h2>Reverting: {{ $revision->title }} <small>(to: {{ Date::TimeAgo($revision->created_at) }})</small></h2>
    <div class="well">
        @form(wiki/revert)
            @hidden(id $revision)
            <p>Continuing will revert this page to the revision displayed below. Are you sure you want to continue?</p>
            @text(reason) = Reason for revert
            @submit = Revert to this Revision
        @endform
    </div>
    @if ($revision->wiki_object->type_id == \App\Models\Wiki\WikiType::UPLOAD)
        <div class="wiki-image">
            <img src="{{ $revision->getUpload()->getResourceFileName() }}" alt="{{ $revision->title }}">
        </div>
    @endif
    <div class="bbcode">
        {!! $revision->content_html !!}
    </div>
    @include('wiki.view.revision-categories', ['revision' => $revision])
@endsection
