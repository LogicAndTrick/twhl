<a id="comments"></a>
{? $cc = $comments->count(); ?}
<h2>
    {{ $cc == 0 ? '' : $cc }} Comment{{ $cc == 1 ? '' : 's' }}
</h2>

<div class="comment-list">
    @foreach ($comments->sortBy('created_at') as $comment)
        <div class="slot" id="comment-{{ $comment->id }}">
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
                    <a class="pull-right" href="#comment-{{ $comment->id }}">Comment #{{ $comment->id }}</a>
                </div>
            </div>
            <div class="slot-main">
                @if ($comment->hasTemplate())
                    @include('comments.templates.'.$comment->getTemplate(), [ 'comment' => $comment, 'obj' => $comment->getTemplateArticleObject() ])
                @else
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
@elseif (\App\Models\Comments\Comment::canCreate($article_type))
    <h3>Add New Comment</h3>

    @if (isset($inject_add))
        @foreach ($inject_add as $v => $d)
            @include($v, $d)
        @endforeach
    @endif

    @include('comments.create', [ 'article' => $article, 'article_type' => $article_type, 'article_id' => $article_id, 'text' => '', 'comment' => null ])
@elseif (!Auth::user())
    <p>
        You must log in to post a comment.
        You can <a href="{{ act('auth', 'login') }}">login</a> or <a href="{{ act('auth', 'register') }}">register a new account</a>.
    </p>
@else
    <p>
        You don't have permission to post comments here.
    </p>
@endif