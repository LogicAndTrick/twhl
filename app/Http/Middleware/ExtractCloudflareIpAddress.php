<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExtractCloudflareIpAddress
{
    public function handle(Request $request, Closure $next): Response
    {
        if (($ip = $request->header('CF-Connecting-IP')) !== null) {
            $request->server->set('REMOTE_ADDR', $ip);
        }
        return $next($request);
    }
}
