<?php namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class StartupServiceProvider extends ServiceProvider {

	public function boot()
	{
        if (app('config')->get('app.debug'))
        {
            DB::connection()->enableQueryLog();
        }
	}

}
