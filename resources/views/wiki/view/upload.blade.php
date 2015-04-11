<h2>{{ $revision->title }}</h2>
@if (!$revision->is_active)
    <div class="alert alert-warning">
        You are viewing an older revision of this image. Only the current revision is embedded on other pages.
        <a href="{{ act('wiki', 'page', $revision->slug) }}">Click here</a> to see the current revision of this image.
    </div>
@endif
<div class="wiki-image">
    <img src="{{ $upload->getResourceFileName() }}" alt="{{ $revision->title }}">
</div>

<h4>Upload Details</h4>
<dl class="dl-horizontal dl-wide">
    <dt>File Size</dt><dd>{{ format_filesize($revision->getFileSize()) }}</dd>
    <dt>Image Width</dt><dd>{{ $revision->getImageWidth() }}</dd>
    <dt>Image Height</dt><dd>{{ $revision->getImageHeight() }}</dd>
    <dt>BBCode (TWHL only)</dt><dd>[img:{{ $revision->getEmbedSlug() }}]</dd>
    <dt>Embed URL (dynamic)</dt><dd><a href="{{ act('wiki', 'embed', $revision->getEmbedSlug(), 'current.'.$upload->extension) }}">{{ act('wiki', 'embed', $revision->getEmbedSlug(), 'current.'.$upload->extension) }}</a></dd>
    <dt>Embed URL (permalink)</dt><dd><a href="{{ $upload->getResourceFileName() }}">{{ $upload->getResourceFileName() }}</a></dd>
</dl>

<h3>Upload Information</h3>
<div class="bbcode">
    {!! $revision->content_html !!}
</div>
@include('wiki.view.revision-categories', ['revision' => $revision])