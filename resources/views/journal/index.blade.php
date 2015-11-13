@extends('app')

@section('content')
    <hc>
        @if (permission('JournalCreate'))
            <a href="{{ act('journal', 'create') }}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-remove"></span> Create new journal</a>
        @endif
        <h1>Journals</h1>
        {!! $journals->render() !!}
    </hc>
    @foreach ($journals as $journal)
        <h2><a href="{{ act('journal', 'view', $journal->id) }}">{{ $journal->user->name }}</a></h2>
        <div class="bbcode">{!! $journal->content_html !!}</div>
    @endforeach
@endsection