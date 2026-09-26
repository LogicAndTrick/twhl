<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as BaseVerifier;
use Illuminate\Http\Request;
use Symfony\Component\Security\Core\Util\StringUtils;

class VerifyCsrfToken extends BaseVerifier {

    protected $except = [
        'search/*'
    ];

    protected function inExceptArray($request)
    {
        if ($request->is('api/*')) {
            // only let api requests through with no csrf validation if they have an api key or authorization header
            return $this->hasApiCredentials($request);
        }
        return parent::inExceptArray($request);
    }

    private function hasApiCredentials(Request $request): bool
    {
        return !empty($request->get('api_key')) || !empty($request->bearerToken()) || !empty($request->header('Authorization'));
    }
}
