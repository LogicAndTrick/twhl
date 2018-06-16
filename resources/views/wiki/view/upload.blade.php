
<h1>
    <span class="fa fa-upload"></span>
    {{ $revision->getNiceTitle() }}
    <small class="pull-right">
        @if (!$revision->wiki_object->canEdit())
            <span class="fa fa-lock" title="You do not have access to edit this page."></span>
        @elseif ($revision->wiki_object->isProtected())
            <span class="fa fa-lock faded" title="This page is protected."></span>
        @endif
        Last edited @date($revision->created_at) by @avatar($revision->user inline)
    </small>
</h1>

<ol class="breadcrumb">
    <li><a href="{{ url('/wiki') }}">Wiki</a></li>
    <li class="active">View Upload</li>
</ol>

@if (!$revision->is_active)
    <div class="alert alert-warning">
        You are viewing an older revision of this image. Only the current revision is embedded on other pages.
        <a href="{{ act('wiki', 'page', $revision->slug) }}">Click here</a> to see the current revision of this image.
    </div>
@endif

@if ($upload->isEmbeddable())
    <div class="wiki-image">
        @if ($upload->isImage())
                <img src="{{ $upload->getResourceFileName() }}" alt="{{ $revision->getNiceTitle() }}">
        @elseif ($upload->isVideo())
            <video src="{{ $upload->getResourceFileName() }}" controls>
                Your browser doesn't support embedded video. Use the embed URL below to download it.
            </video>
        @elseif ($upload->isAudio())
            <audio src="{{ $upload->getResourceFileName() }}" controls>
                Your browser doesn't support embedded audio. Use the embed URL below to download it.
            </audio>
        @endif
    </div>
@endif

<div class="text-center">
    <a class="btn btn-success btn-lg" href="{{ $upload->getEmbeddableFileName() }}">
        <span class="fa fa-download"></span> Download
    </a>
</div>

<h4>Upload Details</h4>
<dl class="dl-horizontal dl-wide">
    <dt>File Size</dt><dd>{{ format_filesize($revision->getFileSize()) }}</dd>
    @if ($upload->isImage())
        <dt>Image Width</dt><dd>{{ $revision->getImageWidth() }}</dd>
        <dt>Image Height</dt><dd>{{ $revision->getImageHeight() }}</dd>
        <dt>BBCode (TWHL only)</dt><dd>[img:{{ $revision->title }}]</dd>
    @endif
    @if ($upload->isVideo())
        <dt>BBCode (TWHL only)</dt><dd>[video:{{ $revision->title }}]</dd>
    @endif
    @if ($upload->isAudio())
        <dt>BBCode (TWHL only)</dt><dd>[audio:{{ $revision->title }}]</dd>
    @endif
    <dt>Embed URL (dynamic)</dt><dd><a href="{{ $upload->getEmbeddableFileName() }}">{{ $upload->getEmbeddableFileName() }}</a></dd>
    <dt>Embed URL (permalink)</dt><dd><a href="{{ $upload->getResourceFileName() }}">{{ $upload->getResourceFileName() }}</a></dd>
</dl>

<h3>Upload Information</h3>
<div class="wiki bbcode">
    {!! $revision->content_html !!}
</div>
@include('wiki.view.revision-categories', ['revision' => $revision])