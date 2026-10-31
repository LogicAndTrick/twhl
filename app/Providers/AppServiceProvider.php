<?php

namespace App\Providers;

use App\Helpers\TwhlUserProvider;
use App\Http\Middleware\LimitGuestPagination;
use App\Models\Accounts\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Pulse\Facades\Pulse;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        RateLimiter::for('global', function (Request $request) {
            $user = $request->user();
            if ($user) return Limit::perMinute(120)->by('user:' . $user->getAuthIdentifier());
            return Limit::perMinute(60)->by('ip:' . $request->ip());
        });
        Auth::provider('twhl', function ($app, array $config) {
            return new TwhlUserProvider($app['hash'], User::class);
        });
        Paginator::useBootstrap();
        Paginator::currentPageResolver(LimitGuestPagination::pageResolver(...));

        Gate::define('viewPulse', function (User $user) {
            return $user->id == 1983;
        });
        Pulse::user(fn (User $user) => [
            'name' => $user->name,
            'extra' => $user->email . " • " . $user->last_access_ip,
            'avatar' => $user->getAvatarUrl('small')
        ]);
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'Illuminate\Contracts\Auth\Registrar',
            'App\Services\Registrar'
        );
        $this->app->bind(
            'Illuminate\Pagination\LengthAwarePaginator',
            'App\Helpers\LimitedLengthAwarePaginator'
        );
        $this->app->singleton(HtmlSanitizer::class, function() {
            return new HtmlSanitizer(
                new HtmlSanitizerConfig()
                    ->allowSafeElements()
                    ->allowLinkSchemes(['http', 'https', 'ftp', 'mailto'])
                    ->allowAttribute('class', '*')
                    ->allowAttribute('id', '*')
                    ->allowAttribute('style', '*')
                    ->allowAttribute('data-embed-type', ['div'])
                    ->allowAttribute('data-vault-id', ['div'])
                    ->allowAttribute('data-info', ['span'])
                    ->allowAttribute('data-youtube-id', ['div'])
                    ->withMaxInputLength(pow(2, 32))
            );
        });
    }
}
