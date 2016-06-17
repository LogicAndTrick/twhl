<?php

namespace App\Http\Middleware;

use App\Models\Accounts\ApiKey;
use Closure;
use Auth;

class ApiKeyAuthenticate
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
        if ($request->is('api/*') && !$request->user()) {
            $api_key = $request->get('api_key');
            if (!$api_key) {
                $headers = getallheaders();
                if (isset($headers['Authorization'])) $api_key = $headers['Authorization'];
            }
            if ($api_key) {
                $key = ApiKey::where('key', '=', $api_key)
                    ->leftJoin('users as u', 'u.id', '=', 'api_keys.user_id')
                    ->whereNull('u.deleted_at')
                    ->select('api_keys.*')
                    ->first();
                if ($key && !!$key->user_id) {
                    Auth::setUser($key->user);
                }
            }
        }
        return $next($request);
    }
}
