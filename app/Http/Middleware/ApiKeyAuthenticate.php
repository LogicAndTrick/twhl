<?php

namespace App\Http\Middleware;

use App\Models\Accounts\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiKeyAuthenticate
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->is('api/*') && !$request->user()) {
            $api_key = $request->get('api_key');
            if (!$api_key) {
                $api_key = $request->bearerToken() ?? $request->header('Authorization');
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
