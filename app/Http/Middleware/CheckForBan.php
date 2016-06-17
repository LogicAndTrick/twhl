<?php namespace App\Http\Middleware;

use App\Models\Accounts\Ban;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckForBan {

    /**
     * Handle an incoming request.
     * @param \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
	public function handle(Request $request, Closure $next)
	{
        if ($this->shouldCheckForBan($request)) {
            if ($this->isBanned($request)) {
                if (($request->ajax() && ! $request->pjax()) || $request->wantsJson()) {
                    return response()->json([
                        'message' => 'This account is banned.'
                    ])->setStatusCode(422);
                }
                return redirect('/ban/index');
            }
        }
        return $next($request);
	}

    /**
     * Check if we should check for a ban.
     * The ban controller is excluded so we don't get into a loop
     * @param Request $request
     * @return bool
     */
    protected function shouldCheckForBan(Request $request)
    {
        return !$request->is('ban/*') && !$request->is('auth/logout');
    }

    /**
     * Check if the current client is banned (either by user id, or by IP address)
     * @param Request $request
     * @return bool
     */
    protected function isBanned(Request $request)
    {
        $id = !Auth::user() ? -1 : Auth::user()->id;
        $ip = $request->ip();
        $now = Carbon::create();

        $activeBan = Ban::where('created_at', '<=', $now)
            ->whereRaw('(ends_at IS NULL OR ends_at >= ?)', [$now])
            ->whereRaw('(user_id = ? OR ip = ?)', [$id, $ip])
            ->first();

        return $activeBan != null;
    }
}
