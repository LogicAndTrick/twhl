@if (!$user_review && $item->canReview())
    <div class="alert alert-info">
        <p class="mb-0">
            Want to post a detailed review instead of a simple star rating?

            <a class="btn btn-primary btn-xs" href="{{ act('vault-review', 'create', $item->id) }}">
                <span class="fa fa-star"></span>
                Click here!
            </a>
        </p>
    </div>
@endif