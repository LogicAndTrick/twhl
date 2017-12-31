@title('Journal: '.$journal->getTitle().' by '.$journal->user->name)
@extends('app')

@section('content')
    <h1>
        {{ $journal->getTitle() }}
        @if ($journal->isEditable())
            <a href="{{ act('journal', 'delete', $journal->id) }}" class="btn btn-outline-danger btn-xs"><span class="fa fa-remove"></span> Delete</a>
            <a href="{{ act('journal', 'edit', $journal->id) }}" class="btn btn-outline-primary btn-xs"><span class="fa fa-pencil"></span> Edit</a>
        @endif
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ act('journal', 'index') }}">Journals</a></li>
        <li class="active">View Journal</li>
    </ol>
    <div class="slot">
        <div class="slot-heading">
            <div class="slot-avatar hidden-md-up">
                @avatar($journal->user small show_name=false)
            </div>
            <div class="slot-title hidden-md-up">
                @avatar($journal->user text)
            </div>
            <div class="slot-subtitle">
                Posted @date($journal->created_at)
            </div>
        </div>
        <div class="slot-row">
            <div class="slot-left hidden-sm-down">
                @avatar($journal->user full)
            </div>
            <div class="slot-main">
                <div class="bbcode">{!! $journal->content_html !!}</div>
            </div>
        </div>
    </div>
    @include('comments.list', [ 'article' => $journal, 'comments' => $comments, 'article_type' => \App\Models\Comments\Comment::JOURNAL, 'article_id' => $journal->id ])
@endsection