@title('Journals')
@extends('app')

@section('content')
    <h1>
        Journals
        @if (permission('JournalCreate'))
            <a href="{{ act('journal', 'create') }}" class="btn btn-primary btn-xs"><span class="fa fa-plus"></span> Create new journal</a>
        @endif
    </h1>

    @if ($user)
        <ol class="breadcrumb">
            <li><a href="{{ act('journal', 'index') }}">Journals</a></li>
            <li class="active">Posted by @avatar($user inline)</li>
        </ol>
    @endif

    {!! $journals->render() !!}

    <div class="journal-list">
        @foreach ($journals as $journal)
            <div class="slot" id="journal-{{ $journal->id }}">
                <div class="slot-heading">
                    <div class="slot-avatar">
                        @avatar($journal->user small show_name=false)
                    </div>
                    <div class="slot-title">
                        <a href="{{ act('journal', 'view', $journal->id) }}">{{ $journal->getTitle() }}</a>
                        @if ($journal->isEditable())
                            <a href="{{ act('journal', 'delete', $journal->id) }}" class="btn btn-outline-danger btn-xs">
                                <span class="fa fa-remove"></span>
                                <span class="hidden-xs-down">Delete</span>
                            </a>
                            <a href="{{ act('journal', 'edit', $journal->id) }}" class="btn btn-outline-primary btn-xs">
                                <span class="fa fa-pencil"></span>
                                <span class="hidden-xs-down">Edit</span>
                            </a>
                        @endif
                    </div>
                    <div class="slot-subtitle">
                        @avatar($journal->user text) &bull;
                        @date($journal->created_at) &bull;
                        <a href="{{ act('journal', 'view', $journal->id) }}">
                            <span class="fa fa-comment"></span>
                            {{ $journal->stat_comments }} comment{{$journal->stat_comments==1?'':'s'}}
                        </a>
                    </div>
                </div>
                <div class="slot-main">
                    <div class="bbcode">{!! $journal->content_html !!}</div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="footer-container">
        {!! $journals->render() !!}
    </div>
@endsection