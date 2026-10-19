<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('update:competitions')->hourly();
        $schedule->command('update:motm')->daily();
        $schedule->command('cloudflare:reload')->daily();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // global middleware
        $middleware->use([
            // modified laravel default global middlewares
            \App\Http\Middleware\BlockAi::class,
            \Illuminate\Http\Middleware\ValidatePathEncoding::class,
            \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
            // \Illuminate\Http\Middleware\TrustHosts::class,
            \Illuminate\Http\Middleware\TrustHosts::class,
            \Monicahq\Cloudflare\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            // \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            // \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,

            // modified laravel default web middlewares - I want these for api calls too
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            //\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            //\Illuminate\Routing\Middleware\SubstituteBindings::class,

            // my custom stuff
            \App\Http\Middleware\UpdateUserAccessDetails::class,
            \App\Http\Middleware\ApiKeyAuthenticate::class,
            \App\Http\Middleware\CheckForBan::class,
            \App\Http\Middleware\ConvertLegacyAccount::class,
            \App\Http\Middleware\LimitGuestPagination::class,

            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':global',
        ]);

        // i'm not sure why i'm bothering with this seeing as all my middlewares are global...
        $middleware->group('web', []);
        $middleware->group('api', []);

        $middleware->alias([
            'permission' => \App\Http\Middleware\Permission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        \Sentry\Laravel\Integration::handles($exceptions);

        // type errors for controller parameters - probably remove this when routing is set up better.
        $exceptions->dontReportWhen(\App\Helpers\Errors::isControllerBindingTypeError(...));
        $exceptions->render(function (\TypeError $typeError, Request $request) {
            if (\App\Helpers\Errors::isControllerBindingTypeError($typeError)) {
                return response()->view('errors.404', [], 404);
            }
        });

        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })
    ->create();
