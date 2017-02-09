@title('Journals')
@extends('app')

@section('content')
    <hc>
        @if (permission('JournalCreate'))
            <a href="{{ act('journal', 'create') }}" class="btn btn-primary btn-xs"><span class="fa fa-plus"></span> Create new journal</a>
        @endif
        <h1>Journals</h1>
        @if ($user)
            <ol class="breadcrumb">
                <li><a href="{{ act('journal', 'index') }}">Journals</a></li>
                <li class="active">Posted by @avatar($user inline)</li>
            </ol>
        @endif
        {!! $journals->render() !!}
    </hc>
    <ul class="media-list">
        @foreach ($journals as $journal)
            <li class="media media-panel" id="journal-{{ $journal->id }}">
              <div class="media-left">
                <div class="media-object">
                    @avatar($journal->user small show_border=false show_name=false)
                </div>
              </div>
              <div class="media-body">
                <div class="media-heading">
                    @if ($journal->isEditable())
                        <a href="{{ act('journal', 'delete', $journal->id) }}" class="btn btn-danger btn-xs"><span class="fa fa-remove"></span> Delete</a>
                        <a href="{{ act('journal', 'edit', $journal->id) }}" class="btn btn-primary btn-xs"><span class="fa fa-pencil"></span> Edit</a>
                    @endif
                    <h2><a href="{{ act('journal', 'view', $journal->id) }}">{{ $journal->getTitle() }}</a></h2>
                    <span class="visible-xs-inline">@avatar($journal->user inline)</span><span class="hidden-xs">@avatar($journal->user text)</span> &bull;
                    @date($journal->created_at)
                </div>
                <div class="bbcode">{!! $journal->content_html !!}</div>
                <div class="media-footer">
                    <a href="{{ act('journal', 'view', $journal->id) }}" class="btn btn-xs btn-link link">
                        <span class="fa fa-comment"></span>
                        {{ $journal->stat_comments }} comment{{$journal->stat_comments==1?'':'s'}}
                    </a>
                </div>
              </div>
            </li>
        @endforeach
    </ul>
    <div class="footer-container">
        {!! $journals->render() !!}
    </div>
@endsection