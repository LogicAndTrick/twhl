@extends('app')

@section('content')
    @include('wiki.nav', ['revision' => $revision])
    <h2>{{ $revision->title }}</h2>
    @form(wiki/edit upload=true)
        @hidden(id $revision)
        @if ($revision->wiki_object->type_id == \App\Models\Wiki\WikiType::PAGE)
            @text(title $revision) = Page Title
        @elseif ($revision->wiki_object->type_id == \App\Models\Wiki\WikiType::UPLOAD)
            @file(file) = Choose File (leave blank to keep the existing file)
        @endif
        @textarea(content_text $revision) = Page Content
        @text(message) = Description of Edit
        @submit = Edit Page
    @endform
@endsection
