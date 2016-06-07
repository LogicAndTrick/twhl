@title('Delete wiki page')
@extends('app')

@section('content')
    @include('wiki.nav', ['revision' => $revision])
    <hc>
        <h1>Delete: {{ $revision->getNiceTitle() }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/wiki') }}">Wiki</a></li>
            <li><a href="{{ act('wiki', 'page', $revision->slug) }}">{{ $revision->getNiceTitle() }}</a></li>
            <li><a href="{{ act('wiki', 'history', $revision->slug) }}">History</a></li>
            <li class="active">Delete Page</li>
        </ol>
    </hc>
    @form(wiki/delete)
        @hidden(id $revision->wiki_object)
        <p>Are you sure you want to delete this wiki page? This will make all content and history of this page invisible to all users.</p>
        @submit = Delete this wiki page
    @endform
@endsection
