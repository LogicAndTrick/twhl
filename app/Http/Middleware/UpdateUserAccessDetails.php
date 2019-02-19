<?php namespace App\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Auth;

class UpdateUserAccessDetails {

    /**
     * Handle an incoming request.
     * @param \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
	public function handle(Request $request, Closure $next)
	{
        if ($this->shouldUpdate($request)) {
            $this->update($request);
        }
        return $next($request);
	}

    /**
     * Check if we should update the user access details on this request.
     * AJAX and POST requests are excluded.
     * @param Request $request
     * @return bool
     */
    protected function shouldUpdate(Request $request)
    {
        return Auth::user() != null
            && !$request->isXmlHttpRequest()
            && $request->method() == 'GET'
            && $request->segments()[0] !== 'api';
    }

    /**
     * Update the logged in user details with the new request information.
     * @param Request $request
     */
    protected function update(Request $request)
    {
        $user = Auth::user();

        if (!$request->session()->has('login_time')) {
            $request->session()->put('login_time', Carbon::create());
            $request->session()->put('last_access_time', $user->last_access_time);
            $user->last_login_time = Carbon::create();
            $user->stat_logins++;
        }

        $user->last_access_time = Carbon::create();
        $user->last_access_page = $request->getPathInfo();
        $user->last_access_ip = $request->ip();
        $user->save();
    }
}
