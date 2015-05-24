<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Symfony\Component\Security\Core\Util\StringUtils;

class VerifyCsrfToken extends BaseVerifier {

    protected $excluded_routes = [
        'shout/*'
    ];
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		return parent::handle($request, $next);
	}

    protected function tokensMatch($request)
    {
        return $this->isExcluded($request) || parent::tokensMatch($request);
    }

    protected function isExcluded($request)
    {
        foreach($this->excluded_routes as $route) {
            if ($request->is($route)) return true;
        }
        return false;
    }
}
