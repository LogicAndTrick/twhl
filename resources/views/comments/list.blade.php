<a id="comments"></a>
<hc>
    {? $cc = $comments->count(); ?}
    <h2>
        {{ $cc == 0 ? '' : $cc }} Comment{{ $cc == 1 ? '' : 's' }}
    </h2>
</hc>

<ul class="media-list">
    @foreach ($comments->sortBy('created_at') as $comment)
        <li class="media" id="comment-{{ $comment->id }}">
            <div class="media-left">
                <div class="media-object">
                    @if ($comment->user)
                        @avatar($comment->user small show_name=false show_border=true)
                    @endif
                </div>
            </div>
            <div class="media-body">
                @if ($comment->hasTemplate())
                    @include('comments.templates.'.$comment->getTemplate(), [ 'comment' => $comment, 'obj' => $comment->getTemplateArticleObject() ])
                @else
                    <div class="media-heading">
                        @if($comment->isDeletable())
                            <a href="{{ act('comment', 'delete', $comment->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> <span class="hidden-xs">Delete</span></a>
                        @endif
                        @if($comment->isEditable($comments))
                            <a href="{{ act('comment', 'edit', $comment->id) }}" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-pencil"></span> <span class="hidden-xs">Edit</span></a>
                        @endif
                        @if ($comment->user)
                            @avatar($comment->user text) &bull;
                        @endif
                        @date($comment->created_at)
                        @if ($comment->hasRating())
                            <span class="stars">
                                @foreach ($comment->getRatingStars() as $star)
                                    <img src="{{ asset('images/stars/gold_'.$star.'_16.png') }}" alt="{{ $star }} star" />
                                @endforeach
                            </span>
                        @endif
                    </div>
                    <div class="bbcode">{!! $comment->content_html !!}</div>
                @endif
            </div>
        </li>
    @endforeach
</ul>

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