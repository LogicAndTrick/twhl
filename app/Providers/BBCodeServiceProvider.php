<?php namespace App\Providers;

use App\Helpers\BBCode\Parser;
use Illuminate\Support\ServiceProvider;

class BBCodeServiceProvider extends ServiceProvider {

    protected $defer = true;

	public function register()
	{
        $this->app->singleton('bbcode', function($app) {
            return new Parser(app('config')->get('bbcode'));
        });
	}

    public function provides()
    {
        return [
            'bbcode'
        ];
    }


}
