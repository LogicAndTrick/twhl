<h2>Page not found: {{ $slug }}</h2>
<p>
    This page doesn't exist on the Wiki.
</p>
@if (permission('WikiCreate'))
    <p>You can create it if you think it is missing.</p>
    @form(wiki/create)
        @text(title $slug_title) = Page Title
        @textarea(content_text) = Page Content
        @submit = Create Page
    @endform
@endif
