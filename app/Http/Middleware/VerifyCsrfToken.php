<?php namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as BaseVerifier;
use Illuminate\Http\Request;
use App\Http\Middleware\ApiKeyAuthenticate;

class VerifyCsrfToken extends BaseVerifier {

    protected $except = [
        'search/*'
    ];

    protected function inExceptArray($request)
    {
        if ($request->is('api/*')) {
            // only let api requests through with no csrf validation
            // if they authenticated with a valid api key
            return $this->hasApiCredentials($request);
        }
        return parent::inExceptArray($request);
    }

    private function hasApiCredentials(Request $request): bool
    {
        return ApiKeyAuthenticate::getAuthenticatedApiKey($request) !== null;
    }
}
