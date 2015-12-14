<hc>
    <h2 id="comments">Comments</h2>
</hc>

<div>
    @foreach ($comments as $comment)
        <div class="comment-container" id="comment-{{ $comment->id }}">
            <div class="comment-info">
                @if ($comment->user)
                    @avatar($comment->user small show_border=true)
                @endif
            </div>
            <div class="comment-body">
                @if ($comment->hasTemplate())
                    @include('comments.templates.'.$comment->getTemplate(), [ 'comment' => $comment, 'obj' => $comment->getTemplateArticleObject() ])
                @else
                    <div class="comment-meta">
                        @if($comment->isDeletable())
                            <a href="{{ act('comment', 'delete', $comment->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> <span class="hidden-xs">Delete</span></a>
                        @endif
                        @if($comment->isEditable($comments))
                            <a href="{{ act('comment', 'edit', $comment->id) }}" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-pencil"></span> <span class="hidden-xs">Edit</span></a>
                        @endif
                        @if ($comment->hasRating())
                            <span class="stars">
                                @foreach ($comment->getRatingStars() as $star)
                                    <img src="{{ asset('images/stars/gold_'.$star.'_16.png') }}" alt="{{ $star }} star" />
                                @endforeach
                            </span>
                        @endif
                        @date($comment->created_at)
                    </div>
                    <div class="bbcode">{!! $comment->content_html !!}</div>
                @endif
            </div>
        </div>
    @endforeach
</div>

@if ($article->commentsIsLocked())
    <div class="alert alert-info text-center">
        <h4>Comments are locked</h4>
    </div>
@else
    <h3>Add New Comment</h3>

    @if (isset($inject_add))
        @foreach ($inject_add as $v => $d)
            @include($v, $d)
        @endforeach
    @endif

    @include('comments.create', [ 'article' => $article, 'article_type' => $article_type, 'article_id' => $article_id, 'text' => '', 'comment' => null ])
@endif