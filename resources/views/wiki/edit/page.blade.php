@title('Edit wiki page: ' . $revision->getNiceTitle())
@extends('app')

@section('content')
    @include('wiki.nav', ['revision' => $revision])

    <h1>
        Edit: {{ $revision->getNiceTitle() }}
        @if (permission('WikiAdmin'))
            <a href="{{ act('wiki', 'delete', $revision->wiki_object->id) }}" class="btn btn-outline-danger btn-xs"><span class="fa fa-remove"></span> Delete</a>
        @endif
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('wiki', 'index') }}">Wiki</a></li>
        <li><a href="{{ act('wiki', 'page', $revision->slug) }}">{{ $revision->getNiceTitle() }}</a></li>
        <li class="active">Edit Page</li>
    </ol>

    @form(wiki/edit upload=true)
        <span class="d-none" id="promptForm"></span>
        @hidden(id $revision)
        @if ($revision->wiki_object->type_id == \App\Models\Wiki\WikiType::PAGE)
            @text(title $revision pattern_name=wiki-title) = Page Title
        @elseif ($revision->wiki_object->type_id == \App\Models\Wiki\WikiType::UPLOAD)
            @text(title $revision pattern_name=wiki-title) = Page Title
            @file(file) = Choose File (leave blank to keep the existing file)
        @endif
        <div class="wikicode-input">
            @textarea(content_text $revision) = Page Content
        </div>
        @text(message) = Description of Edit
        @if (permission('WikiAdmin'))
            @autocomplete(permission_id api/permissions $revision->wiki_object clearable=true) = Permission required to modify
        @endif
        @submit = Edit Page
    @endform

    <script>
        const form = document.getElementById('promptForm');
        if (form) promptWhenClosing(form.closest('form'));
    </script>
@endsection
