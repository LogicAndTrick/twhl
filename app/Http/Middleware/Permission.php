<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Permission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = $request->user();
        if ($permission == 'LoggedIn') {
            if (!$user) return abort(404);
        } else if (is_string($permission)) {
            if (!$user) return abort(404);
            if (!$user->hasPermission($permission)) return abort(404);
        }
        return $next($request);
    }
}
