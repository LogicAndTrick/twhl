<?php

namespace App\Helpers;

use App\Http\Middleware\LimitGuestPagination;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Limit the maximum pagination page for guest users (higher for authenticated users).
 * This is an attempt to prevent scrapers from accessing deep pages.
 * This is the default paginator, but it's overridden in a couple of places to allow unauthenticated users to see important content:
 * - ForumController - see all threads in a forum
 * - ThreadController - see all posts in a thread
 * - WikiController - see all pages in a category
 */
class LimitedLengthAwarePaginator extends LengthAwarePaginator
{
    public function lastPage(): int
    {
        $last = parent::lastPage();
        if (!LimitGuestPagination::shouldBeLimited(request())) return $last;
        return min($last, LimitGuestPagination::getMaxPage());
    }
}
