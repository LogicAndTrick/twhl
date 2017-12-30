<h1>
    <span class="fa fa-frown-o"></span>
    Page not found: {{ $slug }}
</h1>

<ol class="breadcrumb">
    <li><a href="{{ url('/wiki') }}">Wiki</a></li>
    <li class="active">Nonexistent Page</li>
</ol>

<p>
    This page doesn't exist on the Wiki.
</p>

@if (permission('WikiCreate'))
    <p>You can create it if you think it is missing.</p>
    @form(wiki/create)
        @text(title $slug_title) = Page Title
        <div class="wikicode-input">
            @textarea(content_text) = Page Content
        </div>
        @submit = Create Page
    @endform
@endif
