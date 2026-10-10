<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Limit the maximum pagination page for guest users. Logged-in users have a higher limit.
 * This is an attempt to prevent scrapers from accessing deep pages.
 */
class LimitGuestPagination
{
    /** Soft cap for guest users */
    private const MAX_GUEST_PAGE = 100;

    /** Hard cap for authenticated users */
    private const MAX_AUTHENTICATED_PAGE = 1000;

    public static function getMaxPage(): int
    {
        return Auth::check() ? self::MAX_AUTHENTICATED_PAGE : self::MAX_GUEST_PAGE;
    }

    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->shouldBeLimited($request)) {
            $page = $request->query('page');
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page > self::getMaxPage()) abort(404);
        }
        return $next($request);
    }

    public static function shouldBeLimited(Request $request): bool
    {
        // allow non-GET requests, the 'last' page (specific for forum threads)
        if (!$request->isMethod('GET')) return false;
        if ($request->query('page') === 'last') return false;

        // allow the special cases: thread list, thread view, wiki pages
        if (
            $request->is('forum/view/*')
            || $request->is('thread/view/*')
            || $request->is('wiki/page/*')
        ) {
            return false;
        }

        // I tested this on API calls already, it works as expected if you
        // have an API key since this middleware runs after ApiKeyAuthenticate.

        // everything else
        return true;
    }

    public static function pageResolver($pageName = 'page'): int
    {
        $page = request()->query($pageName);

        // page number must be a positive integer
        // if the user is not authenticated, limit the maximum page number

        if (filter_var($page, FILTER_VALIDATE_INT) === false) return 1;
        return clamp((int) $page, 1, self::getMaxPage());
    }
}
