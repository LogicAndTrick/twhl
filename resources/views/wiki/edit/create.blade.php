@title('Create new wiki page')
@extends('app')

@section('content')
    @include('wiki.nav')

    <h1>Create new page</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('wiki', 'index') }}">Wiki</a></li>
        <li class="active">Create Page</li>
    </ol>

    @form(wiki/create)
        <span class="d-none" id="promptForm"></span>
        @text(title $slug_title) = Page Title
        <div class="wikicode-input">
            @textarea(content_text) = Page Content
        </div>
        @submit = Create Page
    @endform

    <script>
        const form = document.getElementById('promptForm');
        if (form) promptWhenClosing(form.closest('form'));
    </script>
@endsection
