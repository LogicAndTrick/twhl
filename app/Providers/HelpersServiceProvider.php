<?php namespace App\Providers;


use Illuminate\Support\ServiceProvider;

class HelpersServiceProvider extends ServiceProvider {

    public function boot()
    {
        //
    }

    public function register()
    {
        require app_path('Helpers/ViewFunctions.php');
    }
}
