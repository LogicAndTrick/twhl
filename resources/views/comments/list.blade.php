<h2>Comments</h2>

@foreach ($comments as $comment)
    <div class="row">
        <div class="col-md-2">
            {{ $comment->user->name }}<br/>
            @if($comment->isEditable($comments))
                <a href="{{ act('comment', 'edit', $comment->id) }}" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-pencil"></span></a>
            @endif
            @if($comment->isDeletable())
            <a href="{{ act('comment', 'delete', $comment->id) }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span></a>
            @endif
        </div>
        <div class="col-md-10">
            @if ($comment->hasRating())
                Rating: {{ $comment->getRating() }} <br/>
            @endif
            {!! $comment->content_html !!}
        </div>
    </div>
@endforeach

<h3>Add New Comment</h3>
@include('comments.create', [ 'article_type' => $article_type, 'article_id' => $article_id, 'text' => '', 'comment' => null ])