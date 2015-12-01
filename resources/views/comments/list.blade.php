<hc>
    <h2 id="comments">Comments</h2>
</hc>

<div>
    @foreach ($comments as $comment)
        <div class="comment-container">
            <div class="comment-info">
                @avatar($comment->user small show_border=true)
            </div>
            <div class="comment-body">
                @if($comment->isDeletable())
                    <a href="{{ act('comment', 'delete', $comment->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Delete</a>
                @endif
                @if($comment->isEditable($comments))
                    <a href="{{ act('comment', 'edit', $comment->id) }}" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-pencil"></span> Edit</a>
                @endif
                @if ($comment->hasRating())
                    <span class="stars">
                        @foreach ($comment->getRatingStars() as $star)
                            <img src="{{ asset('images/stars/gold_'.$star.'_16.png') }}" alt="{{ $star }} star" />
                        @endforeach
                    </span>
                @endif
                <div class="bbcode">{!! $comment->content_html !!}</div>
            </div>
        </div>
    @endforeach
</div>

<h3>Add New Comment</h3>
@include('comments.create', [ 'article_type' => $article_type, 'article_id' => $article_id, 'text' => '', 'comment' => null ])