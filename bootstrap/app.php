<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schedule;

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
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':global',
        ]);

        $middleware->web(
            append: [
                \App\Http\Middleware\UpdateUserAccessDetails::class,
                \App\Http\Middleware\CheckForBan::class,
                \App\Http\Middleware\ConvertLegacyAccount::class,
                \App\Http\Middleware\LimitGuestPagination::class,
            ],
            replace: [
                // we use our own csrf middleware
                \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class => \App\Http\Middleware\VerifyCsrfToken::class,
            ],
            remove: [
                // this logs out users if they change their password, maybe we want to enable this at some point?
                \Illuminate\Session\Middleware\AuthenticateSession::class,
            ]
        );

        $middleware->api(
            append: [
                \App\Http\Middleware\ApiKeyAuthenticate::class,
            ]
        );

        $middleware->alias([
            'permission' => \App\Http\Middleware\Permission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        \Sentry\Laravel\Integration::handles($exceptions);
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })
    ->create();
