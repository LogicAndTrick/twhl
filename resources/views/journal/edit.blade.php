@title('Edit journal post')
@extends('app')

@section('content')
    <h1>Edit journal: {{ $journal->getTitle() }} by @avatar($journal->user inline)</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('journal', 'index') }}">Journals</a></li>
        <li><a href="{{ act('journal', 'view', $journal->id) }}">Journal #{{ $journal->id }}</a></li>
        <li class="active">Edit Journal</li>
    </ol>

    @form(journal/edit)
        @hidden(id $journal)
        @text(title $journal) = Journal Title
        <div class="wikicode-input">
            @textarea(content_text:text $journal) = Journal Content
        </div>
        @submit = Edit Journal
    @endform
@endsection
