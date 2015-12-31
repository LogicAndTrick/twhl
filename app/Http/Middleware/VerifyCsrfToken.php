<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Symfony\Component\Security\Core\Util\StringUtils;

class VerifyCsrfToken extends BaseVerifier {

    protected $except = [
        'shout/*',
        'search/*'
    ];
}
