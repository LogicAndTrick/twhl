
@title('Comments')
@extends('app')

@section('content')

    <h1>
        Comments
    </h1>

    <div class="comment-list">
        @foreach ($comments as $comment)
            {? $article = $articles->get($comment->article_type . $comment->article_id); ?}
            <div class="slot" id="comment-{{ $comment->id }}">
                @if ($article != null)
                    <div class="slot-heading">
                        @if ($comment->user)
                            <div class="slot-avatar">
                                @avatar($comment->user small show_name=false)
                            </div>
                        @endif
                        <div class="slot-title">
                            @avatar($comment->user text)
                            @if ($comment->hasRating())
                                <span class="stars">
                                    @foreach ($comment->getRatingStars() as $star)
                                        <img src="{{ asset('images/stars/rating_'.$star.'.svg') }}" alt="{{ $star }} star" />
                                    @endforeach
                                </span>
                            @endif
                            @if($comment->isDeletable())
                                <a href="{{ act('comment', 'delete', $comment->id) }}" class="btn btn-danger btn-xs"><span class="fa fa-remove"></span> <span class="hidden-xs">Delete</span></a>
                            @endif
                            @if($comment->isEditable($comments))
                                <a href="{{ act('comment', 'edit', $comment->id) }}" class="btn btn-primary btn-xs"><span class="fa fa-pencil"></span> <span class="hidden-xs">Edit</span></a>
                            @endif
                        </div>
                        <div class="slot-subtitle">
                            Commented @date($comment->created_at)
                            in <strong>{{ $comment->getArticleTypeDescription() }}</strong>: <a href="{{ $comment->getArticleUrl() }}">{{ $comment->getArticleTitle($article) }}</a>
                            <span class="pull-right hidden-xs-down">Comment #{{ $comment->id }}</span>
                        </div>
                    </div>
                    <div class="slot-main">
                        @if ($comment->hasTemplate())
                            @include('comments.templates.'.$comment->getTemplate(), [ 'comment' => $comment, 'obj' => $comment->getTemplateArticleObject() ])
                        @else
                            <div class="bbcode {{$comment->user->getClasses()}}">{!! $comment->content_html !!}</div>
                        @endif
                    </div>
                @else
                    <div class="text-center m-2 font-italic">
                        This comment was made on an article that has been deleted.
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    <div class="footer-container">
        {!! $comments->render() !!}
    </div>
@endsection