@extends('app')

@section('content')
    @if (permission('JournalAdmin'))
        <p>
            <a href="{{ act('journal', 'create') }}">Create new journal</a>
        </p>
    @endif
    <h2>Journals</h2>
    @foreach ($journals as $journal)
        <h2><a href="{{ act('journal', 'view', $journal->id) }}">{{ $journal->user->name }}</a></h2>
        <div class="bbcode">{!! $journal->content_html !!}</div>
    @endforeach
    {!! $journals->render() !!}
@endsection