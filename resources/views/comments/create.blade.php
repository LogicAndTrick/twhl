{? $meta = \App\Models\Comments\CommentMeta::GetMetaFor($article_type); ?}
@if (isset($comment))
@form(comment/edit)
    @hidden(id $comment)
@else
    @form(comment/create)
@endif
    @hidden(article_id $article_id)
    @hidden(article_type $article_type)
    @foreach ($meta as $m)
        @if ($article->commentsCanAddMeta($m))
            @if ($m == \App\Models\Comments\CommentMeta::RATING)
                <?php
                    $rating = Request::old('meta_rating');
                    if ($rating === null) $rating = isset($comment) && $comment->hasRating() ? $comment->getRating() : null;
                    if ($rating === null) $rating = 0;
                ?>
                <div class="form-group comment-meta-rating">
                    <label for="rating_val">Rating</label>
                    <select class="form-control" id="rating_val" name="meta_rating">
                        <option value="0" {{ $rating == 0 ? 'selected' : ''}}>Do not rate</option>
                        <option value="1" {{ $rating == 1 ? 'selected' : ''}}>1</option>
                        <option value="2" {{ $rating == 2 ? 'selected' : ''}}>2</option>
                        <option value="3" {{ $rating == 3 ? 'selected' : ''}}>3</option>
                        <option value="4" {{ $rating == 4 ? 'selected' : ''}}>4</option>
                        <option value="5" {{ $rating == 5 ? 'selected' : ''}}>5</option>
                    </select>
                    <div class="stars"
                         data-empty-star="{{ asset('images/stars/rating_empty.svg') }}"
                         data-full-star="{{ asset('images/stars/rating_full.svg') }}"
                    >

                    </div>
                </div>
            @else
                Unknown meta type: {{ $m }}
            @endif
        @endif
    @endforeach
    {? $text = Request::old('text') !== null ? Request::old('text') : (isset($comment) ? $comment->content_text : $text); ?}
    <div class="wikicode-input">
        @textarea(text $text) = Comment Text
    </div>
    @submit = Post Comment
@endform