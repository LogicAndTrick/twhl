<?php namespace App\Providers;

use App\Helpers\ExtensionMimeTypeGuesser;
use App\Helpers\PaginationPresenter;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
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
