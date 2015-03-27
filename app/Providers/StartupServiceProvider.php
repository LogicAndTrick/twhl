<?php namespace App\Providers;

use App\Helpers\ExtensionMimeTypeGuesser;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class StartupServiceProvider extends ServiceProvider {

	public function boot()
	{
        if (app('config')->get('app.debug'))
        {
            DB::connection()->enableQueryLog();
        }
	}

	public function register()
	{
        $guesser = MimeTypeGuesser::getInstance();
        $guesser->register(new ExtensionMimeTypeGuesser());
	}

}
