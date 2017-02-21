<?php namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Route;

class RouteServiceProvider extends ServiceProvider {

    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
	{
        $this->mapApiRoutes();
        $this->mapWebRoutes();
	}

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'namespace' => $this->namespace,
        ], function ($router) {
            require app_path('Http/routes.php');
        });
        //Route::namespace($this->namespace)
        //   ->group(function ($router) {
        //       require app_path('Http/routes.php');
        //   });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {

    }

}
