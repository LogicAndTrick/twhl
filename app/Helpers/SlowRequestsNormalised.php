<?php

namespace App\Helpers;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Laravel\Pulse\Recorders\SlowRequests;

class SlowRequestsNormalised extends SlowRequests {
    // override this so we can handle {_missing} gracefully in our
    // ApiController and others.
    protected function resolveRoutePath(Request $request): array {
        $route = $request->route();
        if ($route->isFallback) {
            $path = $request->path();
            $via = $route->getActionName();
            if ($request->is('api/*') && $route->hasParameter('_missing')) {
                $key = str_replace(['/', '-'], '_', $route->parameter('_missing'));
                $method = strtolower($request->getMethod());
                $via = ApiController::class . "@{$method}_{$key}";
            }
            return [$path, $via];
        }
        return parent::resolveRoutePath($request);
    }
}
