@if (!$revision->is_active)
    <div class="alert alert-warning">
        You are viewing an older revision of this wiki page. The current revision may be more detailed and up-to-date.
        <a href="{{ act('wiki', 'page', $revision->slug) }}">Click here</a> to see the current revision of this page.
    </div>
@endif
<div class="bbcode">
    {!! $revision->content_html !!}
</div>