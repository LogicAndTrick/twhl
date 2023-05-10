<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',
		'App\Http\Middleware\VerifyCsrfToken',
        'App\Http\Middleware\UpdateUserAccessDetails',
		'App\Http\Middleware\ApiKeyAuthenticate',
		'App\Http\Middleware\CheckForBan',
		'App\Http\Middleware\ConvertLegacyAccount',
	];

	/**
	 * The application's middleware aliases.
	 *
	 * @var array
	 */
	protected $middlewareAliases = [
		'auth' => 'App\Http\Middleware\Authenticate',
		'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
		'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
        'permission' => 'App\Http\Middleware\Permission'
	];

}
