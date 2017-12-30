@title('Create journal post')
@extends('app')

@section('content')
    <h1>Create journal post</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('journal', 'index') }}">Journals</a></li>
        <li class="active">Create Journal</li>
    </ol>

    @form(journal/create)
        @text(title) = Journal Title
        <div class="wikicode-input">
            @textarea(text) = Journal Content
        </div>
        @submit = Create Journal
    @endform
@endsection
