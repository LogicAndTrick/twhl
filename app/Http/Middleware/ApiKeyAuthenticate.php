<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Accounts\ApiKey;

class ApiKeyAuthenticate
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $key = null;
        if ($request->is('api/*') && !$request->user()) {
            $api_key = $request->post('api_key')
                ?? $request->json('api_key')
                ?? $request->bearerToken()
                ?? $request->header('Authorization');
            if ($api_key != null && is_string($api_key) && strlen($api_key) > 0) {
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
        self::setAuthenticatedApiKey($request, $key ?? null);
        return $next($request);
    }

    const CurrentApiKeyAttribute = 'current_api_key';

    static function setAuthenticatedApiKey(Request $request, ApiKey|null $key): void
    {
        $request->attributes->set(self::CurrentApiKeyAttribute, $key);
    }

    public static function getAuthenticatedApiKey(Request $request): ApiKey|null
    {
        return $request->attributes->get(self::CurrentApiKeyAttribute, null);
    }
}
