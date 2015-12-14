@if (!$user_review && $item->reviewsAllowed())
    <div class="alert alert-info">
        <p>
            Want to post a detailed review instead of a simple star rating?

            <a class="btn btn-primary btn-xs" href="{{ act('vault-review', 'create', $item->id) }}">
                <span class="glyphicon glyphicon-star"></span>
                Click here!
            </a>
        </p>
    </div>
@endif