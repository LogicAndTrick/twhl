<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Auth;

class ConvertLegacyAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->shouldCheckForLegacyAccount($request)) {
            if ($this->isLegacyAccount($request)) {
                return redirect('/auth/convert');
            }
        }
        return $next($request);
    }

    protected function shouldCheckForLegacyAccount(Request $request) {
        return Auth::check()
            && !$request->isXmlHttpRequest()
            && $request->method() == 'GET'
            && !$request->is('auth/*')
            && !$request->is('shout/*')
            && !$request->is('wiki/page/TWHL:_Site_Rules');
    }

    protected function isLegacyAccount(Request $request) {
        $user = Auth::user();
        return !!$user->legacy_password;
    }
}
